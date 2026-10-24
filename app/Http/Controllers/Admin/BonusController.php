<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Bonus\BonusLedger;
use App\Services\Bonus\SimpleBonusProgressService;
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
    protected SimpleBonusProgressService $progress;

    public function __construct(SimpleBonusProgressService $progress)
    {
        $this->progress = $progress;
    }

    public function show(int $clientId)
    {
        $client = DB::table('tbl_clients')->where('id', $clientId)->whereNull('deleted_at')->first();

        if (! $client) {
            abort(404);
        }

        $progress = $this->progress->forClient($clientId, true);

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
            'eligible'    => $progress->credits >= $progress->required && ! $progress->hasOpenBonus,
            'remainingToEarn' => max(0, $progress->required - $progress->credits),
        ]);
    }

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
}
