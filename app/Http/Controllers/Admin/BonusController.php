<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Bonus\BonusLedger;
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
            'total'        => count($rows),
            'excellent'    => 0,
            'compliant'    => 0,
            'recovering'   => 0,
            'late'         => 0,
            'spiral'       => 0,
            'hard_reset'   => 0,
            'eligible'     => 0,
            'near'         => 0,
            'open'         => 0,
            'postponed'    => 0,
            'no_debt'      => 0,
            'with_debt'    => 0,
            'avg_progress' => 0,
            'avg_score'    => 0,
            'precise'      => 0,
            'week'         => 0,
            'late_habit'   => 0,
            'slow'         => 0,
            'very_slow'    => 0,
        ];

        $progressSum = 0;
        $scoreSum    = 0;

        foreach ($rows as $row) {
            /** @var BonusProgress $p */
            $p = $row['progress'];
            $progressSum += $p->progress;
            $scoreSum    += $p->score;

            if ($p->pattern !== null && isset($summary[$p->pattern])) {
                $summary[$p->pattern]++;
            }

            if (isset($summary[$p->status])) {
                $summary[$p->status]++;
            }
            if (in_array($p->status, ['excellent', 'compliant'], true)) {
                $summary['no_debt']++;
            }
            if ($p->eligible) {
                $summary['eligible']++;
            }
            if ($p->progress >= max(1, (int) floor($p->required * 0.75))) {
                $summary['near']++;
            }
            if ($p->hasOpenBonus) {
                $summary['open']++;
            }
        }

        // Bonus rows waiting for a decision (postponed ones have their own counter).
        $summary['open']      = DB::table('tbl_client_bonuses')->whereIn('status', ['pending', 'approved'])->count();
        $summary['postponed'] = DB::table('tbl_client_bonuses')->where('status', 'postponed')->count();

        $summary['with_debt']    = $summary['total'] - $summary['no_debt'];
        $summary['avg_progress'] = $summary['total'] > 0 ? (int) round($progressSum / $summary['total']) : 0;
        $summary['avg_score']    = $summary['total'] > 0 ? (int) round($scoreSum / $summary['total']) : 0;

        // Customers outside the program (inactive, satellite, another type).
        $summary['excluded'] = DB::table('tbl_clients')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('is_active', 0)->orWhereNotIn('client_type', BonusSettings::clientTypes());
            })
            ->count();

        $pendingClients = DB::table('tbl_client_bonuses')
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('client_id')
            ->flip()
            ->all();

        $postponedClients = DB::table('tbl_client_bonuses')
            ->where('status', 'postponed')
            ->pluck('client_id')
            ->flip()
            ->all();

        $rows = array_values(array_filter($rows, function ($row) use ($filter, $postponedClients, $pendingClients) {
            /** @var BonusProgress $p */
            $p = $row['progress'];

            return match ($filter) {
                'excellent', 'compliant', 'recovering', 'late', 'spiral', 'hard_reset'
                              => $p->status === $filter,
                'catchup'     => $p->status === 'recovering',      // old link
                'precise', 'week', 'late_habit', 'slow', 'very_slow'
                              => $p->pattern === $filter,
                'no_debt'     => in_array($p->status, ['compliant', 'excellent'], true),
                'debt'        => ! in_array($p->status, ['compliant', 'excellent'], true),
                'eligible'    => $p->eligible,
                'near'        => $p->progress >= max(1, (int) floor($p->required * 0.75)) && ! $p->eligible,
                'open_bonus'  => $p->hasOpenBonus,
                'postponed'   => isset($postponedClients[(int) $row['client']->id]),
                'pending_bonus' => isset($pendingClients[(int) $row['client']->id]),
                default       => true,
            };
        }));

        $sort = (string) $request->input('sort', 'progress_desc');
        switch ($sort) {
            case 'progress_asc':
                usort($rows, fn ($a, $b) => $a['progress']->progress <=> $b['progress']->progress);
                break;
            case 'name':
                usort($rows, fn ($a, $b) => strcmp($a['client']->name, $b['client']->name));
                break;
            case 'clean':
                usort($rows, fn ($a, $b) => $b['progress']->cleanMonths <=> $a['progress']->cleanMonths);
                break;
            case 'score_asc':
                usort($rows, fn ($a, $b) => $a['progress']->score <=> $b['progress']->score);
                break;
            case 'score_desc':
                usort($rows, fn ($a, $b) => $b['progress']->score <=> $a['progress']->score);
                break;
            default:
                usort($rows, fn ($a, $b) => $b['progress']->progress <=> $a['progress']->progress);
        }

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($rows, $filter);
        }

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
            'sort'      => $sort,
            'settings'  => BonusSettings::all(),
            'enabled'   => BonusSettings::enabled(),
        ]);
    }

    /**
     * Download the filtered list as a CSV that opens in Excel with Arabic text intact.
     */
    protected function exportCsv(array $rows, string $filter)
    {
        $labels = [
            'excellent'  => 'ممتاز',
            'compliant'  => 'منتظم',
            'recovering' => 'عائد للانتظام',
            'late'       => 'متأخر',
            'spiral'     => 'دوامة',
            'hard_reset' => 'متعثر',
            'excluded'   => 'غير مشمول',
        ];

        $patterns = [
            'precise'    => 'دقيق',
            'week'       => 'خلال أسبوع',
            'late_habit' => 'متأخر عادةً',
            'slow'       => 'مماطل',
            'very_slow'  => 'مماطل جداً',
        ];

        $fileName = 'bonus-' . ($filter !== '' ? $filter : 'all') . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows, $labels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // BOM so Excel reads UTF-8
            fputcsv($out, ['الاسم', 'الهاتف', 'الحالة', 'نمط الدفع', 'درجة الالتزام', 'التأخير المعتاد (يوم)', 'النقاط', 'أشهر نظيفة', 'آخر شهر محتسب', 'استدراك مستخدم في', 'عليه ديون']);

            foreach ($rows as $row) {
                /** @var BonusProgress $p */
                $p = $row['progress'];
                fputcsv($out, [
                    $row['client']->name,
                    $row['client']->phone,
                    $labels[$p->status] ?? $p->status,
                    $patterns[$p->pattern] ?? '-',
                    $p->score,
                    $p->medianLag ?? '-',
                    $p->progress . '/' . $p->required,
                    $p->cleanMonths,
                    $p->lastCreditedMonth ?? '-',
                    $p->lastCatchupMonth ?? '-',
                    in_array($p->status, ['compliant', 'excellent'], true) ? 'لا' : 'نعم',
                ]);
            }

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(int $clientId)
    {
        $client = DB::table('tbl_clients')->where('id', $clientId)->whereNull('deleted_at')->first();

        if (! $client) {
            abort(404);
        }

        $progress = $this->progress->forClient($clientId);

        $bonuses = DB::table('tbl_client_bonuses as b')
            ->leftJoin('admins as a', 'a.id', '=', 'b.approved_by')
            ->where('b.client_id', $clientId)
            ->orderByDesc('b.id')
            ->get(['b.*', 'a.name as approved_by_name']);

        // What the admin needs to know before pressing anything.
        $balance = (float) DB::table('tbl_invoices')
            ->where('client_id', $clientId)
            ->whereNull('deleted_at')
            ->sum('remaining_amount');

        $adjustments = DB::table('tbl_client_bonus_adjustments as adj')
            ->leftJoin('admins as a', 'a.id', '=', 'adj.created_by')
            ->where('adj.client_id', $clientId)
            ->orderByDesc('adj.id')
            ->limit(5)
            ->get(['adj.*', 'a.name as created_by_name']);

        $openBonus = $bonuses->first(fn ($b) => in_array($b->status, ['pending', 'approved', 'postponed'], true));

        return view('dashbord.bonus.show', [
            'client'      => $client,
            'progress'    => $progress,
            'bonuses'     => $bonuses,
            'adjustments' => $adjustments,
            'balance'     => $balance,
            'openBonus'   => $openBonus,
            'eligible'    => $progress->progress >= $progress->required && ! $progress->hasOpenBonus,
            'remainingToEarn' => max(0, $progress->required - $progress->progress),
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
            'notify'   => [
                'earned'    => \App\Services\TelegramConfig::get('telegram_notify_bonus_earned', '1') !== '0',
                'postponed' => \App\Services\TelegramConfig::get('telegram_notify_bonus_postponed', '1') !== '0',
                'redeemed'  => \App\Services\TelegramConfig::get('telegram_notify_bonus_redeemed', '1') !== '0',
            ],
        ]);
    }

    // ------------------------------------------------------------ actions

    public function grant(Request $request, int $clientId)
    {
        $data = $request->validate([
            'notes'   => 'nullable|string|max:255',
            'approve' => 'nullable|in:0,1',
        ]);

        return $this->run(function () use ($request, $clientId, $data) {
            app(BonusLedger::class)->grant(
                $clientId,
                auth('admin')->id(),
                $data['notes'] ?? null,
                ($data['approve'] ?? '0') === '1'
            );

            return 'تم منح البونص';
        }, $clientId);
    }

    public function approve(int $bonusId)
    {
        return $this->run(function () use ($bonusId) {
            app(BonusLedger::class)->approve($bonusId, auth('admin')->id());

            return 'تم اعتماد البونص';
        }, $this->clientOfBonus($bonusId));
    }

    public function postpone(Request $request, int $bonusId)
    {
        $data = $request->validate([
            'reason'       => 'required|string|max:255',
            'target_month' => 'nullable|regex:/^\d{4}-(0[1-9]|1[0-2])$/',
        ], [
            'reason.required' => 'سبب التأجيل مطلوب.',
        ]);

        $clientId = $this->clientOfBonus($bonusId);

        return $this->run(function () use ($bonusId, $data) {
            app(BonusLedger::class)->postpone(
                $bonusId,
                auth('admin')->id(),
                $data['reason'],
                $data['target_month'] ?? null
            );

            return 'تم تأجيل الشهر المجاني';
        }, $clientId);
    }

    public function destroy(Request $request, int $bonusId)
    {
        $reason = $request->input('reason');
        $clientId = $this->clientOfBonus($bonusId);

        return $this->run(function () use ($bonusId, $reason) {
            app(BonusLedger::class)->deletePending($bonusId, auth('admin')->id(), $reason);

            return 'تم حذف البونص المعلّق';
        }, $clientId);
    }

    public function cancel(Request $request, int $bonusId)
    {
        $reason = $request->input('reason');

        return $this->run(function () use ($bonusId, $reason) {
            app(BonusLedger::class)->cancel($bonusId, auth('admin')->id(), $reason);

            return 'تم إلغاء البونص';
        }, $this->clientOfBonus($bonusId));
    }

    public function apply(int $bonusId)
    {
        return $this->run(function () use ($bonusId) {
            $result = app(BonusLedger::class)->applyNow($bonusId, auth('admin')->id());

            return "تم إصدار فاتورة الشهر المجاني #{$result['invoice_id']} باستحقاق {$result['due_date']}";
        }, $this->clientOfBonus($bonusId));
    }

    public function points(Request $request, int $clientId)
    {
        $data = $request->validate([
            'points' => 'required|integer|between:-24,24|not_in:0',
            'reason' => 'nullable|string|max:255',
        ]);

        return $this->run(function () use ($clientId, $data) {
            app(BonusLedger::class)->addPoints($clientId, (int) $data['points'], auth('admin')->id(), $data['reason'] ?? null);

            return 'تم تعديل نقاط البونص';
        }, $clientId);
    }

    public function reset(Request $request, int $clientId)
    {
        $reason = $request->input('reason');

        return $this->run(function () use ($clientId, $reason) {
            app(BonusLedger::class)->resetCycle($clientId, auth('admin')->id(), $reason);

            return 'تم إعادة تعيين الدورة';
        }, $clientId);
    }

    public function log()
    {
        $bonuses = DB::table('tbl_client_bonuses as b')
            ->leftJoin('tbl_clients as c', 'c.id', '=', 'b.client_id')
            ->orderByDesc('b.id')
            ->limit(300)
            ->get(['b.*', 'c.name as client_name']);

        $adjustments = DB::table('tbl_client_bonus_adjustments as a')
            ->leftJoin('tbl_clients as c', 'c.id', '=', 'a.client_id')
            ->orderByDesc('a.id')
            ->limit(300)
            ->get(['a.*', 'c.name as client_name']);

        return view('dashbord.bonus.log', [
            'bonuses'     => $bonuses,
            'adjustments' => $adjustments,
        ]);
    }

    /** Wrap an action so a rule violation becomes a friendly message, not a 500. */
    protected function run(callable $action, ?int $clientId = null)
    {
        try {
            $message = $action();

            Cache::flush();

            $redirect = $clientId
                ? redirect()->route('admin.bonus.show', $clientId)
                : redirect()->route('admin.bonus.index');

            return $redirect->with('success', $message);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('bonus action failed: '.$e->getMessage());

            return back()->with('error', 'تعذّر تنفيذ العملية: '.$e->getMessage());
        }
    }

    protected function clientOfBonus(int $bonusId): ?int
    {
        $clientId = DB::table('tbl_client_bonuses')->where('id', $bonusId)->value('client_id');

        return $clientId ? (int) $clientId : null;
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'bonus_enabled'                 => 'nullable|in:0,1',
            'bonus_months_required'         => 'required|integer|min:1|max:120',
            'bonus_grace_days'              => 'required|integer|min:0|max:31',
            'bonus_catchup_per_cycle'       => 'required|integer|min:0|max:12',
            'bonus_recovery_months'         => 'required|integer|min:1|max:12',
            'bonus_hard_reset_after_months' => 'required|integer|min:0|max:12',
            'bonus_spiral_after_months'     => 'required|integer|min:1|max:12',
            'bonus_count_start_date'        => 'required|date',
            'bonus_require_zero_balance'    => 'nullable|in:0,1',
            'bonus_invoice_notes'           => 'nullable|string|max:255',
            'bonus_client_types'            => 'required|in:internet,both',
        ]);

        // The form offers "internet only" (default) or "internet + satellite".
        $validated['bonus_client_types'] = $validated['bonus_client_types'] === 'both'
            ? 'internet,satellite'
            : 'internet';

        $validated['bonus_enabled']               = $request->input('bonus_enabled', '0');
        $validated['bonus_require_zero_balance']  = $request->input('bonus_require_zero_balance', '0');

        foreach ($validated as $key => $value) {
            BonusSettings::set($key, $value);
        }

        // Telegram toggles for the three bonus events.
        foreach (['earned', 'postponed', 'redeemed'] as $event) {
            \App\Models\AppConfig::query()->updateOrCreate(
                ['key' => 'telegram_notify_bonus_'.$event],
                ['value' => $request->input('notify_'.$event, '0') === '1' ? '1' : '0']
            );
        }
        \App\Services\TelegramConfig::clearCache();

        return redirect()
            ->route('admin.bonus.settings')
            ->with('success', 'تم حفظ إعدادات البونص');
    }
}
