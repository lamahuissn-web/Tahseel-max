<?php

namespace App\Http\Resources;

use App\Models\Admin\Invoice;
use App\Models\Clients;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = class_basename($this->type);

        // $data = $this->data;

        // if (isset($data['client_id'])) {
        //     $client = Clients::find($data['client_id']);
        //     if ($client) {
        //         $data['client_name'] = $client->name;
        //     }
        // }

        // if (isset($data['invoice_id'])) {
        //     $invoice = Invoice::find($data['invoice_id']);
        //     if ($invoice) {
        //         $data['invoice_number'] = $invoice->number;
        //         $data['invoice_amount'] = $invoice->amount;
        //     }
        // }
        return [
            'id' => $this->id,
            'type' => $type,
            'message' => $this->data['message'] ?? $this->getDefaultMessage($type),
            'read_at' => $this->read_at,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d h:i A'),
            'data' => $this->data,
            // 'data' => $data,
        ];
    }

    private function getDefaultMessage($type)
    {
        return match ($type) {
            'NewClientAddedNotification' => 'تم إضافة عميل جديد',
            'InvoiceReminderNotification' => 'تنبيه بفاتورة مستحقة',
            'InvoicePaidNotification' => 'تم دفع الفاتورة',
            'InvoiceRedoNotification' => 'إعادة إصدار الفاتورة',
            'AccountTransferNotification' => 'تم تحويل المبلغ',
            'AccountTransferRedoNotification' => 'تم إعادة تحويل المبلغ',
            default => 'إشعار جديد',
        };
    }
}
