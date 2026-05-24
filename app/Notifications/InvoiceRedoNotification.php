<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceRedoNotification extends Notification
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
            'message' => $this->message,
            'invoice_id' => $this->invoice->id,
            'amount' => $this->amount,
            'user_name' => $this->user->name,
            'type' => 'invoice_redo',
            'category' => 'invoices',
            'color' => 'warning'
        ];
    }
}
