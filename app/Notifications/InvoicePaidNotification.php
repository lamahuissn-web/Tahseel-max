<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicePaidNotification extends Notification
{
    use Queueable;
    protected $invoice;
    protected $amount;
    protected $user;
    protected $message;

    /**
     * Create a new notification instance.
     */

    public function __construct($invoice, $amount, $user, $message)
    {
        $this->invoice = $invoice;
        $this->amount = $amount;
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'invoice_id' => $this->invoice->id,
            'amount' => $this->amount,
            'user_name' => $this->user->name,
            'type' => 'invoice_paid',
            'category' => 'invoices',
            'color' => 'success'
        ];
    }
}
