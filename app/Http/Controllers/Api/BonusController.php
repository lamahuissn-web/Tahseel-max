<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Bonus\BonusSettings;
use App\Services\Bonus\SimpleBonusProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Feature 022 — the bonus section inside the mobile app (read-only).
 *
 * Administration only (roles 1 + 7), read-only on purpose: no grant, no approval,
 * no issuance. Money decisions stay on the web screen where the audit trail lives.
 *
 * One engine, one rule: months credited while the customer's total balance stayed
 * zero at each month's boundary. Two states, no score, no habit band.
 */
class BonusController extends Controller
{
    private const STATUS_LABELS = [
        'clean' => 'منتظم',
        'late'  => 'متأخر',
    ];

    private const BONUS_LABELS = [
        'pending'   => 'بانتظار القرار',
        'approved'  => 'معتمد (لم يُصدر)',
        'postponed' => 'مؤجّل',
        'redeemed'  => 'مُصدر',
        'cancelled' => 'ملغى',
    ];

    public function __construct(private readonly SimpleBonusProgressService $progress)
    {
    }

    public function summary()
    {
        $data = Cache::remember('bonus_api_summary_v2', now()->addMinutes(5), function () {
            $map = $this->progress->forClients($this->inScopeIds(), false);

            $clean = 0;
            $late  = 0;
            $eligible = 0;

            foreach ($map as $p) {
                $p->status === 'clean' ? $clean++ : $late++;
                if ($p->eligible) {
                    $eligible++;
                }
            }

            $required = BonusSettings::monthsRequired();

            return [
                'total'             => count($map),
                'clean'             => $clean,
                'late'              => $late,
                'eligible'          => $eligible,
                'required'          => $required,
                'eligible_cost'     => $this->progress->costOf(array_keys(array_filter(
                    $map,
                    fn ($p) => $p->eligible
                ))),
                'open'              => (int) DB::table('tbl_client_bonuses')
                    ->whereIn('status', ['pending', 'approved', 'postponed'])->count(),
                'issued'            => (int) DB::table('tbl_client_bonuses')
                    ->where('status', 'redeemed')->count(),
            ];
        });

        return $this->wrap(['enabled' => BonusSettings::enabled()] + $data);
    }

    public function clients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search'   => 'nullable|string|max:255',
            'filter'   => 'nullable|in:all,eligible,verge,clean,late',
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        $search   = trim((string) $request->input('search', ''));
        $filter   = (string) $request->input('filter', 'all');
        $page     = max(1, (int) $request->input('page', 1));
        $perPage  = (int) $request->input('per_page', 25);

        $map = $this->progress->forClients($this->inScopeIds(), false);

        $clients = DB::table('tbl_clients')
            ->whereIn('id', array_keys($map))
            ->get(['id', 'name', 'phone', 'price'])
            ->keyBy('id');

        $openBonuses = $this->openBonuses(array_keys($map));

        $rows = [];
        foreach ($map as $id => $p) {
            $client = $clients[$id] ?? null;
            if ($client === null) {
                continue;
            }

            if ($search !== '' && ! $this->matches($client->name, $client->phone, $search)) {
                continue;
            }

            $row = $this->row($p, $client, $openBonuses[$id] ?? null);

            if ($filter === 'eligible' && ! $p->eligible) {
                continue;
            }
            if ($filter === 'verge' && ($p->eligible || $p->required - $p->credits > 2 || $p->required - $p->credits < 1)) {
                continue;
            }
            if ($filter === 'clean' && $p->status !== 'clean') {
                continue;
            }
            if ($filter === 'late' && $p->status !== 'late') {
                continue;
            }

            $rows[] = $row;
        }

        usort($rows, fn ($a, $b) => [$b['credits'], $a['last_credited_month'] ?? '']
            <=> [$a['credits'], $b['last_credited_month'] ?? '']);

        $total  = count($rows);
        $offset = ($page - 1) * $perPage;

        return $this->wrap([
            'enabled'  => BonusSettings::enabled(),
            'required' => BonusSettings::monthsRequired(),
            'rows'     => array_slice($rows, $offset, $perPage),
            'meta'     => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    public function client(int $id)
    {
        $client = DB::table('tbl_clients')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first(['id', 'name', 'phone', 'price']);

        if ($client === null) {
            return $this->fail('الزبون غير موجود.', 404);
        }

        $progress = $this->progress->forClient($id, true);
        $open = $this->openBonuses([$id])[$id] ?? null;

        return $this->wrap($this->row($progress, $client, $open) + [
            'enabled'  => BonusSettings::enabled(),
            'readOnly' => true,
            'months' => array_map(fn ($m) => [
                'month'    => $m['month'],
                'due'      => $m['due'],
                'credited' => $m['credited'],
                'balance'  => $m['balance'],
                'paid_at'  => $m['paid_at'],
            ], $progress->months),
        ]);
    }

    // ------------------------------------------------------------------ helpers

    /** @return array<string,mixed> */
    private function row($p, object $client, ?string $openBonus = null): array
    {
        return [
            'id'                 => $p->clientId,
            'name'               => $client->name,
            'phone'              => (string) ($client->phone ?? ''),
            'price'              => (float) ($client->price ?? 0),
            'progress'           => $p->credits,
            'required'           => $p->required,
            'remaining'          => $p->remainingToEarn(),
            'balance'            => round($p->balance, 2),
            'eligible'           => $p->eligible,
            'status'             => $p->status,
            'statusLabel'        => self::STATUS_LABELS[$p->status] ?? $p->status,
            'lastCreditedMonth'  => $p->lastCreditedMonth,
            'nextEligibleMonth'  => $p->nextEligibleMonth,
            'openBonus'          => $openBonus,
            'openBonusLabel'     => $openBonus === null ? null : self::BONUS_LABELS[$openBonus],
        ];
    }

    private function matches(?string $name, ?string $phone, string $needle): bool
    {
        $needle = mb_strtolower($needle);

        return str_contains(mb_strtolower((string) $name), $needle)
            || str_contains((string) $phone, $needle);
    }

    /** @param array<int,int> $ids @return array<int,string> */
    private function openBonuses(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $rows = DB::table('tbl_client_bonuses')
            ->whereIn('client_id', $ids)
            ->whereIn('status', ['pending', 'approved', 'postponed'])
            ->orderBy('id')
            ->get(['client_id', 'status']);

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->client_id] = (string) $row->status;
        }

        return $map;
    }

    /** @return array<int,int> */
    private function inScopeIds(): array
    {
        return DB::table('tbl_clients')
            ->whereNull('deleted_at')
            ->where('is_active', '1')
            ->where('client_type', 'internet')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function wrap(array $data)
    {
        return response()->json([
            'result'  => true,
            'message' => null,
            'data'    => $data,
        ]);
    }

    private function fail(string $message, int $status)
    {
        return response()->json([
            'result'  => false,
            'message' => $message,
            'data'    => (object) [],
        ], $status);
    }
}
