<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReminderNotification extends Notification
{
    use Queueable;
    private $invoice;
    /**
     * Create a new notification instance.
     */
    public function __construct($invoice)
    {
        $this->invoice = $invoice;
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable)
    {
        return [
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->amount,
            'remaining_amount' => $this->invoice->remaining_amount,
            'paid_amount' => $this->invoice->amount - $this->invoice->remaining_amount,
            'due_date' => $this->invoice->due_date,
            'client' => $this->invoice->client_id,
            'subscription' => $this->invoice->subscription_id,
            'message' => " الفاتورة رقم #{$this->invoice->invoice_number} تجاوزت تاريخ الاستحقاق!",
        ];
    }
}
