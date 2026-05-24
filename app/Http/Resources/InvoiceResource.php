<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => ($this->client?->client_type == 'satellite' ? 'SA-' : 'IN-') . $this->invoice_number,
            'client_id' => $this->client_id,
            'client_name' => $this->client?->name,
            'client_phone' => $this->client?->phone,
            'client_address' => $this->client?->address1,
            'subscription_id' => $this->subscription_id,
            'subscription' => $this->subscription ? $this->subscription->name : trans('invoices.service'),
            'amount' => $this->amount,
            'paid_amount' => $this->paid_amount,
            'remaining_amount' => $this->remaining_amount,
            'due_date' => $this->due_date ?? 'N/A',
            'paid_date' => $this->paid_date ? Carbon::parse($this->paid_date)->format('Y-m-d h:i A') : 'N/A',
            'collected_by' => $this->revenues->isNotEmpty() ? $this->revenues->first()->user->name : null,
            // 'status' => $this->status,
            'status' => 'unpaid',
            'invoice_type' => trans('invoices.'.$this->invoice_type),
            'notes' => $this->notes,
            'currency' => get_app_config_data('currency')
        ];
    }
}
