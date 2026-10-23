<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Bonus\BonusProgress;
use App\Services\Bonus\BonusProgressService;
use App\Services\Bonus\BonusSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Feature 021 Phase 4 — the bonus section inside the mobile app (read-only).
 *
 * Administration only (roles 1 + 7) and read-only on purpose: no grant, no
 * approval, no issuance. Money decisions stay on the web screen where the
 * confirmation flow and the audit trail live.
 */
class BonusController extends Controller
{
    /** Status labels so the app never translates or invents wording. */
    private const STATUS_LABELS = [
        'excellent'  => 'ممتاز في الدفع',
        'compliant'  => 'منتظم',
        'recovering' => 'عائد للانتظام',
        'late'       => 'متأخر',
        'spiral'     => 'دوامة',
        'hard_reset' => 'متعثر',
        'excluded'   => 'غير مشمول',
        'inactive'   => 'غير نشط',
    ];

    private const HABIT_LABELS = [
        'precise'   => '⚡ دقيق',
        'week'      => '✅ خلال أسبوع',
        'late_habit'=> '🕐 متأخر عادةً',
        'slow'      => '🐢 مماطل',
        'very_slow' => '⏳ مماطل جداً',
    ];

    public function summary()
    {
        if (! BonusSettings::enabled()) {
            return $this->wrap(['enabled' => false]);
        }

        $counts = Cache::remember('bonus_api_summary', now()->addMinutes(10), function () {
            $map = $this->allProgress();
            $out = array_fill_keys(array_keys(self::STATUS_LABELS), 0);
            $out['eligible'] = 0;
            $out['open'] = 0;
            $out['total'] = 0;

            foreach ($map as $p) {
                $out['total']++;
                if (isset($out[$p->status])) {
                    $out[$p->status]++;
                }
                if ($p->eligible) {
                    $out['eligible']++;
                }
                if ($p->hasOpenBonus) {
                    $out['open']++;
                }
            }

            $out['postponed'] = DB::table('tbl_client_bonuses')->where('status', 'postponed')->count();
            $out['pending_decision'] = DB::table('tbl_client_bonuses')->whereIn('status', ['pending', 'approved'])->count();
            $out['required'] = BonusSettings::monthsRequired();

            return $out;
        });

        return $this->wrap(['enabled' => true] + $counts);
    }

    public function clients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search'   => 'nullable|string|max:255',
            'filter'   => 'nullable|string|max:32',
            'sort'     => 'nullable|string|max:32',
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        if (! BonusSettings::enabled()) {
            return $this->wrap(['enabled' => false, 'rows' => [], 'meta' => ['total' => 0]]);
        }

        $search   = trim((string) $request->input('search', ''));
        $filter   = (string) $request->input('filter', 'all');
        $sort     = (string) $request->input('sort', 'progress_desc');
        $perPage  = max(1, min((int) $request->input('per_page', 30), 100));
        $page     = max(1, (int) $request->input('page', 1));

        $map = $this->allProgress();

        $query = DB::table('tbl_clients')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->whereIn('client_type', BonusSettings::clientTypes())
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('sas_username', 'like', '%'.$search.'%');
                });
            })
            ->select('id', 'name', 'phone', 'client_type', 'price');

        $postponedIds = DB::table('tbl_client_bonuses')->where('status', 'postponed')->pluck('client_id')->flip()->all();
        $pendingIds   = DB::table('tbl_client_bonuses')->whereIn('status', ['pending', 'approved'])->pluck('client_id')->flip()->all();

        $rows = [];
        foreach ($query->cursor() as $client) {
            $p = $map[(int) $client->id] ?? null;
            if (! $p) {
                continue;
            }
            if (! $this->passes($filter, $p, (int) $client->id, $postponedIds, $pendingIds)) {
                continue;
            }
            $rows[] = ['client' => $client, 'p' => $p];
        }

        $this->sortRows($rows, $sort);

        $total = count($rows);
        $slice = array_slice($rows, ($page - 1) * $perPage, $perPage);
        $openBonus = $this->openBonusFor(array_map(fn ($r) => (int) $r['client']->id, $slice));

        $data = [];
        foreach ($slice as $row) {
            $raw = $openBonus[(int) $row['client']->id] ?? null;
            $data[] = [
                'id'            => (int) $row['client']->id,
                'name'          => $row['client']->name,
                'phone'         => $row['client']->phone,
                'clientType'    => $row['client']->client_type,
                'progress'      => $row['p']->progress,
                'required'      => $row['p']->required,
                'status'        => $row['p']->status,
                'statusLabel'   => self::STATUS_LABELS[$row['p']->status] ?? $row['p']->status,
                'eligible'      => (bool) $row['p']->eligible,
                'habit'         => $row['p']->pattern,
                'habitLabel'    => $row['p']->pattern ? (self::HABIT_LABELS[$row['p']->pattern] ?? $row['p']->pattern) : null,
                'score'         => $row['p']->score,
                'nextEligibleMonth' => $row['p']->nextEligibleMonth,
                'openBonus'     => $raw ? $raw->status : null,
                'openBonusLabel'=> $raw ? $this->bonusStatusLabel($raw->status) : null,
            ];
        }

        return $this->wrap([
            'enabled' => true,
            'rows'    => $data,
            'meta'    => [
                'total'     => $total,
                'page'      => $page,
                'per_page'  => $perPage,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    public function client(int $id)
    {
        if (! BonusSettings::enabled()) {
            return $this->wrap(['enabled' => false]);
        }

        $client = DB::table('tbl_clients')->where('id', $id)->whereNull('deleted_at')->first();

        if (! $client) {
            return $this->fail('الزبون غير موجود.', 404);
        }

        /** @var BonusProgressService $service */
        $service  = app(BonusProgressService::class);
        $progress = $service->forClient($id);

        $bonuses = DB::table('tbl_client_bonuses as b')
            ->leftJoin('admins as a', 'a.id', '=', 'b.approved_by')
            ->where('b.client_id', $id)
            ->orderByDesc('b.id')
            ->get(['b.id', 'b.status', 'b.earned_at', 'b.redeemed_at', 'b.postpone_reason', 'b.postpone_target_month', 'a.name as approved_by_name'])
            ->map(fn ($b) => [
                'id'        => (int) $b->id,
                'status'    => $b->status,
                'label'     => $this->bonusStatusLabel($b->status),
                'earnedAt'  => (string) $b->earned_at,
                'redeemedAt'=> $b->redeemed_at,
                'postponeReason' => $b->postpone_reason,
                'postponeTargetMonth' => $b->postpone_target_month,
                'approvedBy'=> $b->approved_by_name,
            ])->values();

        $balance = (float) DB::table('tbl_invoices')
            ->where('client_id', $id)
            ->whereNull('deleted_at')
            ->sum('remaining_amount');

        return $this->wrap([
            'enabled'      => true,
            'client'       => [
                'id'    => (int) $client->id,
                'name'  => $client->name,
                'phone' => $client->phone,
            ],
            'progress'     => $progress->progress,
            'required'     => $progress->required,
            'status'       => $progress->status,
            'statusLabel'  => self::STATUS_LABELS[$progress->status] ?? $progress->status,
            'habit'        => $progress->pattern,
            'habitLabel'   => $progress->pattern ? (self::HABIT_LABELS[$progress->pattern] ?? $progress->pattern) : null,
            'habitSentence'=> $progress->lagLabel(),
            'score'        => $progress->score,
            'nextEligibleMonth' => $progress->nextEligibleMonth,
            'months'       => $progress->months,
            'balance'      => round($balance, 2),
            'bonuses'      => $bonuses,
            'readOnly'     => true,
        ]);
    }

    // ------------------------------------------------------------- internals

    /** @return array<int,BonusProgress> */
    private function allProgress(): array
    {
        return Cache::remember('bonus_api_map', now()->addMinutes(10), function () {
            $ids = DB::table('tbl_clients')
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->whereIn('client_type', BonusSettings::clientTypes())
                ->pluck('id')
                ->all();

            return app(BonusProgressService::class)->forClients($ids);
        });
    }

    private function passes(string $filter, BonusProgress $p, int $clientId, array $postponedIds, array $pendingIds): bool
    {
        return match ($filter) {
            'all', ''      => true,
            'eligible'     => $p->eligible,
            'near'         => $p->progress >= max(1, (int) floor($p->required * 0.75)) && ! $p->eligible,
            'open'         => $p->hasOpenBonus,
            'postponed'      => isset($postponedIds[$clientId]),
            'pending_bonus'  => isset($pendingIds[$clientId]),
            'debt'         => ! in_array($p->status, ['compliant', 'excellent'], true),
            'excellent', 'compliant', 'recovering', 'late', 'spiral', 'hard_reset'
                           => $p->status === $filter,
            default        => true,
        };
    }

    private function sortRows(array &$rows, string $sort): void
    {
        usort($rows, function ($a, $b) use ($sort) {
            return match ($sort) {
                'progress_asc'  => $a['p']->progress <=> $b['p']->progress,
                'score_desc'    => $b['p']->score <=> $a['p']->score,
                'score_asc'     => $a['p']->score <=> $b['p']->score,
                'name'          => strcmp((string) $a['client']->name, (string) $b['client']->name),
                default         => $b['p']->progress <=> $a['p']->progress,
            };
        });
    }

    /** @return array<int,object> */
    private function openBonusFor(array $clientIds): array
    {
        if ($clientIds === []) {
            return [];
        }

        return DB::table('tbl_client_bonuses')
            ->whereIn('client_id', $clientIds)
            ->whereIn('status', ['pending', 'approved', 'postponed'])
            ->orderBy('id')
            ->get(['client_id', 'status'])
            ->keyBy('client_id')
            ->all();
    }

    private function bonusStatusLabel(string $status): string
    {
        return [
            'pending'   => 'بانتظار القرار',
            'approved'  => 'معتمد (لم يُصدر)',
            'postponed' => 'مؤجّل',
            'redeemed'  => 'مُصدر',
            'cancelled' => 'ملغى',
        ][$status] ?? $status;
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
