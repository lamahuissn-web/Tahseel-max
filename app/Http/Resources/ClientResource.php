<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // استخدام القيمة من subquery إذا كانت موجودة، وإلا استخدام accessor
        $attributes = $this->getAttributes();
        $latestDueDate = isset($attributes['latest_invoice_due_date']) 
            ? $attributes['latest_invoice_due_date'] 
            : ($this->latest_invoice_due_date ?? $this->subscription_date);
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'clientType' => $this->client_type,
            'user' => $this->user,
            'boxSwitch' => $this->box_switch,
            // 'subscriptionDate' => $this->subscription_date,
            'subscriptionDate' => $latestDueDate,
            'startDate' => $this->start_date,
            'address' => $this->address1,
            'subscriptionName' => $this->subscription ? $this->subscription->name : null,
            // 'subscriptionPrice' => $this->subscription ? $this->subscription->price : null,
            'subscriptionPrice' => (string)number_format($this->invoices_sum_remaining_amount ?? 0, 2, '.', ''),
            // 'total_remaining_amount' => $this->invoices_sum_remaining_amount ?? 0,
            'currency' => get_app_config_data('currency')
        ];
    }
}
