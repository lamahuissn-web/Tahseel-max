<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Bonus\BonusProgress;
use App\Services\Bonus\BonusProgressService;
use App\Services\Bonus\BonusSettings;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Feature 021 — «البونص والمكافآت» admin section.
 *
 * Read-only in this phase: the progress number is always computed from the real
 * invoices, never stored, so it can not drift after a restore or a payment edit.
 */
class BonusController extends Controller
{
    protected BonusProgressService $progress;

    public function __construct(BonusProgressService $progress)
    {
        $this->progress = $progress;
    }

    public function index(Request $request)
    {
        $search  = trim((string) $request->input('search', ''));
        $filter  = (string) $request->input('filter', 'all');
        $perPage = 25;

        $query = DB::table('tbl_clients')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->whereIn('client_type', BonusSettings::clientTypes())
            ->select(['id', 'name', 'phone', 'subscription_id']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('name')->get();

        // Progress is pure derivation, so it is cached under a data fingerprint:
        // any payment, invoice edit or bonus change produces a new key immediately
        // (no staleness), while repeat page loads stay instant on a phone.
        $fingerprint = md5(implode('|', [
            (string) DB::table('tbl_invoices')->max('updated_at'),
            (string) DB::table('tbl_invoices')->count(),
            (string) DB::table('tbl_revenues')->count(),
            (string) DB::table('tbl_client_bonuses')->max('updated_at'),
            md5(json_encode(BonusSettings::all())),
        ]));

        $cacheKey = 'bonus.progress.map.'.$fingerprint;

        $progressMap = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($clients) {
            $map = [];

            foreach ($clients->pluck('id')->chunk(400) as $chunk) {
                $map += $this->progress->forClients($chunk->all(), false);
            }

            return $map;
        });

        $rows = [];
        foreach ($clients as $client) {
            /** @var BonusProgress $p */
            $p = $progressMap[$client->id] ?? null;
            if (! $p) {
                continue;
            }

            $rows[] = [
                'client'   => $client,
                'progress' => $p,
            ];
        }

        // Summary numbers are computed before filtering so the cards stay stable.
        $summary = [
            'total'    => count($rows),
            'eligible' => 0,
            'near'     => 0,
            'spiral'   => 0,
            'open'     => 0,
        ];

        foreach ($rows as $row) {
            $p = $row['progress'];
            if ($p->eligible) {
                $summary['eligible']++;
            }
            if ($p->progress >= max(1, (int) floor($p->required * 0.75))) {
                $summary['near']++;
            }
            if ($p->status === 'spiral') {
                $summary['spiral']++;
            }
            if ($p->hasOpenBonus) {
                $summary['open']++;
            }
        }

        $rows = array_values(array_filter($rows, function ($row) use ($filter) {
            /** @var BonusProgress $p */
            $p = $row['progress'];

            return match ($filter) {
                'eligible'    => $p->eligible,
                'near'        => $p->progress >= max(1, (int) floor($p->required * 0.75)) && ! $p->eligible,
                'spiral'      => $p->status === 'spiral',
                'hard_reset'  => $p->status === 'hard_reset',
                'catchup'     => $p->status === 'catchup_used',
                'open_bonus'  => $p->hasOpenBonus,
                'debt'        => $p->status !== 'compliant',
                default       => true,
            };
        }));

        usort($rows, fn ($a, $b) => $b['progress']->progress <=> $a['progress']->progress);

        $page = max(1, (int) $request->input('page', 1));
        $paginator = new LengthAwarePaginator(
            array_slice($rows, ($page - 1) * $perPage, $perPage),
            count($rows),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('dashbord.bonus.index', [
            'paginator' => $paginator,
            'summary'   => $summary,
            'search'    => $search,
            'filter'    => $filter,
            'settings'  => BonusSettings::all(),
            'enabled'   => BonusSettings::enabled(),
        ]);
    }

    public function show(int $clientId)
    {
        $client = DB::table('tbl_clients')->where('id', $clientId)->whereNull('deleted_at')->first();

        if (! $client) {
            abort(404);
        }

        $progress = $this->progress->forClient($clientId);

        $bonuses = DB::table('tbl_client_bonuses')
            ->where('client_id', $clientId)
            ->orderByDesc('id')
            ->get();

        return view('dashbord.bonus.show', [
            'client'   => $client,
            'progress' => $progress,
            'bonuses'  => $bonuses,
        ]);
    }

    public function spiral(Request $request)
    {
        $rows = $this->progress->spiralClients(200);

        return view('dashbord.bonus.spiral', [
            'rows' => $rows,
        ]);
    }

    public function settings()
    {
        return view('dashbord.bonus.settings', [
            'settings' => BonusSettings::all(),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'bonus_enabled'                 => 'nullable|in:0,1',
            'bonus_months_required'         => 'required|integer|min:1|max:120',
            'bonus_grace_days'              => 'required|integer|min:0|max:31',
            'bonus_catchup_per_cycle'       => 'required|integer|min:0|max:12',
            'bonus_hard_reset_after_months' => 'required|integer|min:0|max:12',
            'bonus_spiral_after_months'     => 'required|integer|min:1|max:12',
            'bonus_count_start_date'        => 'required|date',
            'bonus_require_zero_balance'    => 'nullable|in:0,1',
            'bonus_auto_apply_on_approve'   => 'nullable|in:0,1',
            'bonus_invoice_notes'           => 'nullable|string|max:255',
            'bonus_client_types'            => 'required|in:internet,both',
        ]);

        // The form offers "internet only" (default) or "internet + satellite".
        $validated['bonus_client_types'] = $validated['bonus_client_types'] === 'both'
            ? 'internet,satellite'
            : 'internet';

        $validated['bonus_enabled']               = $request->input('bonus_enabled', '0');
        $validated['bonus_require_zero_balance']  = $request->input('bonus_require_zero_balance', '0');
        $validated['bonus_auto_apply_on_approve'] = $request->input('bonus_auto_apply_on_approve', '0');

        foreach ($validated as $key => $value) {
            BonusSettings::set($key, $value);
        }

        return redirect()
            ->route('admin.bonus.settings')
            ->with('success', 'تم حفظ إعدادات البونص');
    }
}
