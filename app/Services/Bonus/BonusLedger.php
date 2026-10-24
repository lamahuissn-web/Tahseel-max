<?php

namespace App\Services\Bonus;

use App\Models\ClientBonus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Feature 021 — every write the bonus program can make.
 *
 * The rules the owner asked for:
 *  - the admin decides: an earned bonus is only a *proposal* until approved;
 *  - approval + auto-apply creates the 0-amount invoice for the next cycle,
 *    carrying that cycle's normal due date;
 *  - a free month is never handed out while the customer still owes money;
 *  - one un-redeemed bonus per customer, ever (no banking).
 *
 * Nothing here touches invoices that already exist, revenue rows or balances.
 */
class BonusLedger
{
    public function __construct(protected BonusProgressService $progress)
    {
    }

    // ------------------------------------------------------------- grant

    public function grant(int $clientId, ?int $adminId, ?string $notes = null, bool $approveNow = false): ClientBonus
    {
        $this->assertInProgram($clientId);

        return DB::transaction(function () use ($clientId, $adminId, $notes, $approveNow) {
            $open = ClientBonus::query()
                ->where('client_id', $clientId)
                ->open()
                ->lockForUpdate()
                ->first();

            if ($open) {
                throw new RuntimeException('يوجد بونص مفتوح لهذا الزبون بالفعل.');
            }

            $bonus = ClientBonus::create([
                'client_id'    => $clientId,
                'earned_at'    => now()->toDateString(),
                'status'       => $approveNow ? ClientBonus::STATUS_APPROVED : ClientBonus::STATUS_PENDING,
                'source'       => 'manual',
                'cycle_months' => BonusSettings::monthsRequired(),
                'created_by'   => $adminId,
                'approved_by'  => $approveNow ? $adminId : null,
                'approved_at'  => $approveNow ? now() : null,
                'notes'        => $notes,
            ]);

            $this->log('bonus_granted', sprintf(
                'منح بونص يدوي - الزبون #%d%s',
                $clientId,
                $notes ? ' | '.$notes : ''
            ), $bonus);

            sendTelegramNotification(
                sprintf('🎁 منح بونص يدوي - الزبون #%d%s', $clientId, $notes ? ' | '.$notes : ''),
                'bonus_granted'
            );

            return $bonus;
        });
    }

    // ----------------------------------------------------------- approve

    /**
     * Record an earned entitlement found by `bonus:sync`.
     * Nothing financial happens here — the row waits for an admin decision.
     */
    public function earn(int $clientId): ClientBonus
    {
        $this->assertInProgram($clientId);

        return DB::transaction(function () use ($clientId) {
            $open = ClientBonus::query()
                ->where('client_id', $clientId)
                ->open()
                ->lockForUpdate()
                ->first();

            if ($open) {
                throw new RuntimeException('يوجد بونص مفتوح لهذا الزبون بالفعل.');
            }

            $bonus = ClientBonus::create([
                'client_id'    => $clientId,
                'earned_at'    => now()->toDateString(),
                'status'       => ClientBonus::STATUS_PENDING,
                'source'       => 'auto',
                'cycle_months' => BonusSettings::monthsRequired(),
                'cycle_start'  => BonusSettings::countStartDate(),
            ]);

            $this->log('bonus_earned', sprintf(
                'استحقاق تلقائي (%d شهر) - الزبون #%d',
                BonusSettings::monthsRequired(),
                $clientId
            ), $bonus);

            return $bonus;
        });
    }

    public function approve(int $bonusId, ?int $adminId): ClientBonus
    {
        return DB::transaction(function () use ($bonusId, $adminId) {
            $bonus = ClientBonus::query()->lockForUpdate()->findOrFail($bonusId);

            if ($bonus->status !== ClientBonus::STATUS_PENDING) {
                throw new RuntimeException('لا يمكن اعتماد هذا البونص في حالته الحالية.');
            }

            $bonus->update([
                'status'      => ClientBonus::STATUS_APPROVED,
                'approved_by' => $adminId,
                'approved_at' => now(),
            ]);

            $this->log('bonus_approved', sprintf('اعتماد بونص #%d - الزبون #%d', $bonus->id, $bonus->client_id), $bonus);

            sendTelegramNotification(
                sprintf('✅ اعتماد شهر مجاني - الزبون #%d', $bonus->client_id),
                'bonus_approved'
            );

            // Approval is a decision about the customer's right, never an issue.
            // The free-month invoice is only created when an admin presses issue.
            return $bonus->fresh();
        });
    }

    // ------------------------------------------------------------ cancel

    public function cancel(int $bonusId, ?int $adminId, ?string $reason = null): ClientBonus
    {
        return DB::transaction(function () use ($bonusId, $adminId, $reason) {
            $bonus = ClientBonus::query()->lockForUpdate()->findOrFail($bonusId);

            if (in_array($bonus->status, [ClientBonus::STATUS_REDEEMED, ClientBonus::STATUS_CANCELLED], true)) {
                throw new RuntimeException('لا يمكن إلغاء بونص مستبدل أو ملغى.');
            }

            $bonus->update([
                'status' => ClientBonus::STATUS_CANCELLED,
                'notes'  => trim(($bonus->notes ? $bonus->notes.' | ' : '').($reason ?? 'تم الإلغاء بواسطة الإدارة')),
            ]);

            $this->log('bonus_cancelled', sprintf('إلغاء بونص #%d - الزبون #%d', $bonus->id, $bonus->client_id), $bonus);

            sendTelegramNotification(
                sprintf('⛔ إلغاء بونص - الزبون #%d%s', $bonus->client_id, $reason ? ' | '.$reason : ''),
                'bonus_cancelled'
            );

            return $bonus->fresh();
        });
    }

    // ---------------------------------------------------------- apply now

    /**
     * Create the 0-amount invoice for the next cycle and close the bonus.
     *
     * @return array{bonus:ClientBonus,invoice_id:int,due_date:string}
     */
    public function applyNow(int $bonusId, ?int $adminId): array
    {
        $result = DB::transaction(function () use ($bonusId, $adminId) {
            $bonus = ClientBonus::query()->lockForUpdate()->findOrFail($bonusId);

            if ($bonus->status === ClientBonus::STATUS_REDEEMED) {
                throw new RuntimeException('تم إصدار هذا البونص مسبقاً.');
            }

            if (! in_array($bonus->status, [ClientBonus::STATUS_APPROVED, ClientBonus::STATUS_POSTPONED], true)) {
                throw new RuntimeException('يجب اعتماد البونص قبل الإصدار.');
            }

            $this->assertInProgram((int) $bonus->client_id);

            // A free month is a 0.00 invoice. If the configured value ever drifts this
            // would create a PAID invoice with no revenue row — understated cash and a
            // month that is not free. Refuse instead of trusting the setting.
            $invoiceValue = BonusSettings::invoiceValue();
            if (abs($invoiceValue) > 0.0001) {
                throw new RuntimeException('قيمة فاتورة الشهر المجاني يجب أن تكون صفراً.');
            }

            if (BonusSettings::requireZeroBalance() && $this->outstanding((int) $bonus->client_id) > 0) {
                throw new RuntimeException('لا يمكن إصدار الشهر المجاني والزبون عليه رصيد غير مدفوع.');
            }

            $dueDate = $this->nextCycleDueDate((int) $bonus->client_id);

            $already = DB::table('tbl_invoices')
                ->where('client_id', $bonus->client_id)
                ->whereNull('deleted_at')
                ->where('invoice_type', 'subscription')
                ->where('due_date', $dueDate)
                ->where('amount', 0)
                ->exists();

            if ($already) {
                throw new RuntimeException("يوجد بالفعل فاتورة صفرية للشهر {$dueDate}.");
            }

            $invoiceId = DB::table('tbl_invoices')->insertGetId([
                'invoice_number'   => $this->nextInvoiceNumber(),
                'client_id'        => $bonus->client_id,
                'subscription_id'  => DB::table('tbl_clients')->where('id', $bonus->client_id)->value('subscription_id'),
                'amount'           => $invoiceValue,
                'paid_amount'      => $invoiceValue,
                'remaining_amount' => 0,
                'enshaa_date'      => now()->toDateString(),
                'due_date'         => $dueDate,
                'status'           => 'paid',
                'auto_generated'   => 0,
                'paid_date'        => now(),
                'notes'            => BonusSettings::invoiceNotes(),
                'invoice_type'     => 'subscription',
                'created_by'       => $adminId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $bonus->update([
                'status'              => ClientBonus::STATUS_REDEEMED,
                'redeemed_at'         => now(),
                'redeemed_invoice_id' => $invoiceId,
                'postpone_reason'     => null,
                'postpone_target_month' => null,
            ]);

            $this->log('bonus_redeemed', sprintf(
                'إصدار شهر مجاني - الزبون #%d | فاتورة #%d | استحقاق %s',
                $bonus->client_id,
                $invoiceId,
                $dueDate
            ), $bonus);

            return [
                'client_id'  => (int) $bonus->client_id,
                'bonus'      => $bonus->fresh(),
                'invoice_id' => $invoiceId,
                'due_date'   => $dueDate,
            ];
        });

        // Deliberately OUTSIDE the transaction: sending Telegram inside it held the
        // invoice locks for the whole HTTP timeout and a failure rolled the issued
        // free month back. The invoice is committed; the notification is best effort.
        $this->notifyIssued((int) $result['client_id'], (int) $result['invoice_id'], (string) $result['due_date']);

        return [
            'bonus'      => $result['bonus'],
            'invoice_id' => $result['invoice_id'],
            'due_date'   => $result['due_date'],
        ];
    }

    /**
     * Best-effort admin notification. Never part of the issuance transaction.
     */
    protected function notifyIssued(int $clientId, int $invoiceId, string $dueDate): void
    {
        sendTelegramNotification(
            sprintf(
                '🎉 إصدار شهر مجاني - الزبون #%d | الفاتورة #%d | استحقاق %s بمبلغ 0$',
                $clientId,
                $invoiceId,
                $dueDate
            ),
            'bonus_redeemed'
        );
    }

    // ------------------------------------------------------ manual points

    public function addPoints(int $clientId, int $points, ?int $adminId, ?string $reason = null): void
    {
        $this->assertInProgram($clientId);

        DB::transaction(function () use ($clientId, $points, $adminId, $reason) {
            DB::table('tbl_client_bonus_adjustments')->insert([
                'client_id'  => $clientId,
                'type'       => 'points',
                'points'     => $points,
                'reason'     => $reason,
                'created_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->log('bonus_points_added', sprintf(
                'تعديل نقاط البونص - الزبون #%d | %+d نقطة%s',
                $clientId,
                $points,
                $reason ? ' | '.$reason : ''
            ));
        });
    }

    // ------------------------------------------------------------- reset

    public function resetCycle(int $clientId, ?int $adminId, ?string $reason = null): void
    {
        DB::transaction(function () use ($clientId, $adminId, $reason) {
            ClientBonus::query()
                ->where('client_id', $clientId)
                ->open()
                ->update(['status' => ClientBonus::STATUS_CANCELLED]);

            DB::table('tbl_client_bonus_adjustments')->insert([
                'client_id'  => $clientId,
                'type'       => 'reset',
                'points'     => 0,
                'reason'     => $reason,
                'created_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->log('bonus_cycle_reset', sprintf(
                'إعادة تعيين دورة البونص - الزبون #%d%s',
                $clientId,
                $reason ? ' | '.$reason : ''
            ));
        });
    }

    // ----------------------------------------------------------- helpers

    public function outstanding(int $clientId): float
    {
        return (float) DB::table('tbl_invoices')
            ->where('client_id', $clientId)
            ->whereNull('deleted_at')
            ->sum('remaining_amount');
    }

    protected function assertInProgram(int $clientId): void
    {
        $client = DB::table('tbl_clients')
            ->where('id', $clientId)
            ->whereNull('deleted_at')
            ->first(['client_type', 'is_active']);

        if ($client === null) {
            throw new RuntimeException('الزبون غير موجود.');
        }

        if ((int) $client->is_active !== 1) {
            throw new RuntimeException('هذا الزبون غير نشط — غير مشمول ببرنامج البونص.');
        }

        if (! in_array($client->client_type, BonusSettings::clientTypes(), true)) {
            throw new RuntimeException('هذا الزبون غير مشمول ببرنامج البونص.');
        }
    }

    /**
     * The free month is the next cycle month, using the same day-of-month as the
     * customer's last subscription invoice.
     */
    public function nextCycleDueDate(int $clientId): string
    {
        $lastDue = DB::table('tbl_invoices')
            ->where('client_id', $clientId)
            ->whereNull('deleted_at')
            ->where('invoice_type', 'subscription')
            ->whereNotNull('due_date')
            ->max('due_date');

        $base = $lastDue ? Carbon::parse($lastDue) : now();

        return $base->copy()->addMonthNoOverflow()->toDateString();
    }

    protected function nextInvoiceNumber(): int
    {
        $last = DB::table('tbl_invoices')
            ->whereRaw("invoice_number REGEXP '^[0-9]+$'")
            ->orderByRaw('CAST(invoice_number AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->value('invoice_number');

        return $last ? ((int) $last + 1) : 1;
    }

    /**
     * Delete a pending bonus that was granted by mistake.
     * Only pending entries can be deleted: they never produced a balance or an invoice.
     */
    public function deletePending(int $bonusId, ?int $adminId, ?string $reason = null): void
    {
        DB::transaction(function () use ($bonusId, $adminId, $reason) {
            $bonus = ClientBonus::query()->lockForUpdate()->findOrFail($bonusId);

            if ($bonus->status !== ClientBonus::STATUS_PENDING) {
                throw new RuntimeException('يمكن حذف البونص المعلّق فقط — استخدم «إلغاء» للبونص المعتمد.');
            }

            // keep the audit trail before the row disappears
            $this->log('bonus_deleted', sprintf(
                'حذف بونص معلّق #%d - الزبون #%d%s (بواسطة #%s)',
                $bonus->id, $bonus->client_id, $reason ? ' | '.$reason : '', $adminId ?? '-'
            ), $bonus);

            $bonus->delete();

            sendTelegramNotification(
                sprintf('🗑 حذف بونص معلّق - الزبون #%d%s', $bonus->client_id, $reason ? ' | '.$reason : ''),
                'bonus_deleted'
            );
        });
    }

    /**
     * Postpone the free month: the right stays open, the money decision waits.
     * Administration only — used when the company's situation asks for it.
     */
    public function postpone(int $bonusId, ?int $adminId, string $reason, ?string $targetMonth = null): ClientBonus
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new RuntimeException('سبب التأجيل مطلوب.');
        }

        if ($targetMonth !== null && ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $targetMonth)) {
            throw new RuntimeException('الشهر المستهدف غير صحيح.');
        }

        return DB::transaction(function () use ($bonusId, $adminId, $reason, $targetMonth) {
            $bonus = ClientBonus::query()->lockForUpdate()->findOrFail($bonusId);

            if (! in_array($bonus->status, [ClientBonus::STATUS_PENDING, ClientBonus::STATUS_APPROVED, ClientBonus::STATUS_POSTPONED], true)) {
                throw new RuntimeException('لا يمكن تأجيل هذا البونص في حالته الحالية.');
            }

            $bonus->update([
                'status'                => ClientBonus::STATUS_POSTPONED,
                'postponed_at'          => now(),
                'postponed_by'          => $adminId,
                'postpone_reason'       => $reason,
                'postpone_target_month' => $targetMonth,
            ]);

            $this->log('bonus_postponed', sprintf(
                'تأجيل بونص #%d - الزبون #%d | %s%s',
                $bonus->id,
                $bonus->client_id,
                $reason,
                $targetMonth ? ' | الشهر المستهدف: '.$targetMonth : ''
            ), $bonus);

            sendTelegramNotification(
                sprintf('⏸ تأجيل شهر مجاني - الزبون #%d | %s', $bonus->client_id, $reason),
                'bonus_postponed'
            );

            return $bonus->fresh();
        });
    }

    protected function log(string $action, string $description, ?ClientBonus $bonus = null): void
    {
        try {
            log_helper($action, $description, array_filter([
                'model'   => $bonus,
                'new_data' => $bonus ? $bonus->fresh()->toArray() : null,
            ]));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('bonus log failed: '.$e->getMessage());
        }
    }
}
