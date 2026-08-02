<?php

namespace App\Services;

use App\Exceptions\SecurePaymentException;
use App\Models\Admin;
use App\Models\Admin\Account;
use App\Models\Admin\FinancialTransaction;
use App\Models\Admin\Invoice;
use App\Models\Admin\MobilePaymentOperation;
use App\Models\Admin\Revenue;
use App\Models\Log as AuditLog;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SecureMobilePaymentService
{
    public function __construct(private readonly MobilePaymentReceiptReconciler $receiptReconciler) {}

    public function collectFullRemaining(
        int $invoiceId,
        Admin $collector,
        string $expectedRemaining,
        string $idempotencyKey,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $this->assertCollectorAuthorized($collector, 'غير مصرح بتسجيل الدفعات');

        $expectedCents = $this->toCents($expectedRemaining);
        if ($expectedCents <= 0) {
            throw new SecurePaymentException(
                'payment_validation_failed',
                'المبلغ المتوقع يجب أن يكون أكبر من صفر',
                422,
            );
        }

        $requestHash = hash('sha256', implode('|', [
            $collector->id,
            $invoiceId,
            $this->formatCents($expectedCents),
        ]));

        $replay = $this->replayExisting($idempotencyKey, $requestHash);
        if ($replay !== null) {
            return $replay;
        }

        try {
            return DB::transaction(function () use (
                $invoiceId,
                $collector,
                $expectedCents,
                $idempotencyKey,
                $requestHash,
                $ipAddress,
                $userAgent,
            ): array {
                $operation = MobilePaymentOperation::query()->create([
                    'reference' => 'PAY-'.Str::upper((string) Str::ulid()),
                    'idempotency_key' => $idempotencyKey,
                    'request_hash' => $requestHash,
                    'invoice_id' => $invoiceId,
                    'collector_id' => $collector->id,
                    'account_id' => $collector->account_id,
                    'expected_remaining' => $this->formatCents($expectedCents),
                    'status' => 'processing',
                ]);

                $invoice = Invoice::query()
                    ->whereKey($invoiceId)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                if (! $invoice) {
                    throw new SecurePaymentException(
                        'invoice_not_found',
                        'الفاتورة غير موجودة',
                        404,
                    );
                }

                $remainingCents = $this->toCents((string) $invoice->remaining_amount);
                if ($invoice->status === 'paid' || $remainingCents <= 0) {
                    throw new SecurePaymentException(
                        'invoice_already_paid',
                        'الفاتورة مدفوعة بالفعل',
                        409,
                    );
                }

                if ($remainingCents !== $expectedCents) {
                    throw new SecurePaymentException(
                        'stale_invoice_balance',
                        'تغير المبلغ المتبقي؛ حدّث الفاتورة وأعد التأكيد',
                        409,
                    );
                }

                $paidCents = $this->toCents((string) ($invoice->paid_amount ?? '0'));
                $invoiceTotalCents = $this->toCents((string) $invoice->amount);
                if ($paidCents + $remainingCents !== $invoiceTotalCents) {
                    throw new SecurePaymentException(
                        'invoice_balance_inconsistent',
                        'بيانات رصيد الفاتورة غير متسقة؛ راجع الإدارة',
                        409,
                    );
                }

                $oldStatus = (string) $invoice->status;
                $now = now();
                $amount = $this->formatCents($remainingCents);

                $invoice->forceFill([
                    'paid_amount' => $this->formatCents($paidCents + $remainingCents),
                    'remaining_amount' => '0.00',
                    'status' => 'paid',
                    'paid_date' => $now,
                    'updated_by' => $collector->id,
                ])->save();

                $revenue = Revenue::query()->create([
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id,
                    'amount' => $amount,
                    'collected_by' => $collector->id,
                    'status' => 'paid',
                    'remaining_amount' => '0.00',
                    'received_at' => $now,
                    'notes' => 'mobile_payment_reference:'.$operation->reference,
                ]);

                $transaction = FinancialTransaction::query()->create([
                    'account_id' => $collector->account_id,
                    'amount' => $amount,
                    'date' => $now->toDateString(),
                    'time' => $now->toTimeString(),
                    'month' => $now->month,
                    'year' => $now->year,
                    'notes' => 'سداد فاتورة عبر التطبيق - '.$operation->reference,
                    'type' => 'qapd',
                    'created_by' => $collector->id,
                ]);

                $operation->forceFill([
                    'client_id' => $invoice->client_id,
                    'amount' => $amount,
                    'revenue_id' => $revenue->id,
                    'financial_transaction_id' => $transaction->id,
                    'status' => 'committed',
                    'received_at' => $now,
                ])->save();

                $committedResult = $this->result($operation, $invoice, $collector, false);
                $operation->forceFill(['response_payload' => $committedResult])->save();

                AuditLog::query()->create([
                    'action' => 'mobile_invoice_paid',
                    'description' => 'Mobile payment committed: '.$operation->reference,
                    'old_data' => json_encode([
                        'paid_amount' => $this->formatCents($paidCents),
                        'remaining_amount' => $amount,
                        'status' => $oldStatus,
                    ]),
                    'new_data' => json_encode([
                        'paid_amount' => $invoice->paid_amount,
                        'remaining_amount' => $invoice->remaining_amount,
                        'status' => $invoice->status,
                        'payment_reference' => $operation->reference,
                    ]),
                    'model_type' => Invoice::class,
                    'model_id' => $invoice->id,
                    'user_id' => $collector->id,
                    'ip_address' => $ipAddress,
                    'user_agent' => $this->boundedUserAgent($userAgent),
                ]);

                $operationIdForReceipt = $operation->id;
                DB::afterCommit(function () use ($operationIdForReceipt): void {
                    try {
                        $this->receiptReconciler->process($operationIdForReceipt);
                    } catch (Throwable $exception) {
                        Log::warning('Post-commit mobile payment receipt failed', [
                            'payment_operation_id' => $operationIdForReceipt,
                            'exception' => get_class($exception),
                        ]);
                    }
                });

                return $committedResult;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            $replay = $this->replayExisting($idempotencyKey, $requestHash);
            if ($replay !== null) {
                return $replay;
            }

            throw $exception;
        }
    }

    public function status(Admin $collector, string $idempotencyKey): array
    {
        $this->assertCollectorAuthorized($collector, 'غير مصرح بالاستعلام عن الدفعات');

        $operation = MobilePaymentOperation::query()
            ->where('idempotency_key', $idempotencyKey)
            ->where('collector_id', $collector->id)
            ->first();

        if (! $operation) {
            throw new SecurePaymentException(
                'payment_not_found',
                'عملية الدفع غير موجودة',
                404,
            );
        }

        return $this->storedResult($operation, true);
    }

    private function replayExisting(string $idempotencyKey, string $requestHash): ?array
    {
        $operation = MobilePaymentOperation::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (! $operation) {
            return null;
        }

        if (! hash_equals($operation->request_hash, $requestHash)) {
            throw new SecurePaymentException(
                'idempotency_conflict',
                'مفتاح المحاولة مستخدم لطلب مختلف',
                409,
            );
        }

        if ($operation->status !== 'committed' || $operation->response_payload === null) {
            throw new SecurePaymentException(
                'payment_in_progress',
                'عملية الدفع ما زالت قيد المعالجة؛ أعد الاستعلام بنفس المفتاح',
                409,
            );
        }

        return $this->storedResult($operation, true);
    }

    private function result(
        MobilePaymentOperation $operation,
        Invoice $invoice,
        Admin $collector,
        bool $replayed,
    ): array {
        return [
            'reference' => $operation->reference,
            'status' => $operation->status,
            'amount' => (string) $operation->amount,
            'collected_at' => $operation->received_at?->toIso8601String(),
            'replayed' => $replayed,
            'collector' => [
                'id' => $collector->id,
                'name' => (string) $collector->name,
            ],
            'invoice' => [
                'id' => $invoice->id,
                'status' => $invoice->status,
                'paid_amount' => $this->formatCents($this->toCents((string) $invoice->paid_amount)),
                'remaining_amount' => $this->formatCents($this->toCents((string) $invoice->remaining_amount)),
            ],
        ];
    }

    private function storedResult(MobilePaymentOperation $operation, bool $replayed): array
    {
        $result = $operation->response_payload;
        if (! is_array($result)) {
            throw new SecurePaymentException(
                'payment_in_progress',
                'عملية الدفع ما زالت قيد المعالجة؛ أعد الاستعلام بنفس المفتاح',
                409,
            );
        }

        $result['replayed'] = $replayed;

        return $result;
    }

    private function toCents(string $amount): int
    {
        if (! preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $amount)) {
            throw new SecurePaymentException(
                'payment_validation_failed',
                'صيغة المبلغ غير صالحة',
                422,
            );
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function formatCents(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }

    private function boundedUserAgent(?string $userAgent): ?string
    {
        if ($userAgent === null) {
            return null;
        }

        return function_exists('mb_strcut')
            ? mb_strcut($userAgent, 0, 255, 'UTF-8')
            : substr($userAgent, 0, 255);
    }

    private function assertCollectorAuthorized(Admin $collector, string $forbiddenMessage): void
    {
        if ((string) $collector->status !== '1' || ! $collector->can('pay_invoice')) {
            throw new SecurePaymentException('payment_forbidden', $forbiddenMessage, 403);
        }

        if ($collector->account_id === null
            || ! Account::query()->whereKey($collector->account_id)->exists()) {
            throw new SecurePaymentException(
                'collector_account_invalid',
                'حساب الجابي المالي غير صالح؛ راجع الإدارة',
                422,
            );
        }
    }
}
