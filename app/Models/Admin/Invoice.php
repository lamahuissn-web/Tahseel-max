<?php

namespace App\Models\Admin;

use App\Models\Clients;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    public $table = 'tbl_invoices';

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    public function client()
    {
        return $this->belongsTo(Clients::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class, 'invoice_id');
    }

    // public function markAsPaid($paymentAmount, $notes = null)
    // {
    //     if ($paymentAmount > $this->amount) {
    //         throw new \Exception(trans('invoices.payment_exceeds_invoice_amount'));
    //     }

    //     $remainingAmount = $this->amount - $paymentAmount;

    //     if ($remainingAmount <= 0) {
    //         $this->update([
    //             'status' => 'paid',
    //             'paid_amount' => $paymentAmount,
    //             'remaining_amount' => 0,
    //             'paid_date' => now()->format('Y-m-d'),
    //             'notes' => $notes,
    //         ]);
    //     } else {
    //         $this->update([
    //             'status' => 'partial',
    //             'paid_amount' => $paymentAmount,
    //             'remaining_amount' => $remainingAmount,
    //             'paid_date' => now()->format('Y-m-d'),
    //             'notes' => $notes,
    //         ]);
    //     }

    //     $admin = auth()->user();
    //     $collectedBy = $admin && $admin->is_employee ? $admin->emp_id : auth()->user()->id;

    //     Revenue::create([
    //         'invoice_id' => $this->id,
    //         'client_id' => $this->client_id,
    //         'amount' => $paymentAmount,
    //         'collected_by' => $collectedBy,
    //         'received_at' => now(),
    //     ]);
    // }


    // public function markAsPaid()
    // {
    //     $this->update(['status' => 'paid']);

    //     $this->generateNextInvoice();
    // }
    // public static function lastInvoiceNumber()
    // {
    //     $lastInvoice = self::latest()->first();

    //     return $lastInvoice ? $lastInvoice->invoice_number + 1 : 1;
    // }

    // private function generateNextInvoice()
    // {
    //     self::create([
    //         'invoice_number' => self::lastInvoiceNumber(),
    //         'client_id' => $this->client_id,
    //         'subscription_id' => $this->subscription_id,
    //         'amount' => $this->amount,
    //         'enshaa_date' => now(),
    //         'due_date' => now()->addMonth(),
    //         'status' => 'unpaid',
    //     ]);
    // }
}
