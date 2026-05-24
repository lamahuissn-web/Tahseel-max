<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountTransferNotification extends Notification
{
    use Queueable;

    protected $fromAccount;
    protected $toAccount;
    protected $amount;
    protected $user;
    protected $message;

    /**
     * Create a new notification instance.
     */

    public function __construct($fromAccount, $toAccount, $amount, $user, $message)
    {
        $this->fromAccount = $fromAccount;
        $this->toAccount = $toAccount;
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
            'from_account' => $this->fromAccount,
            'to_account' => $this->toAccount,
            'amount' => $this->amount,
            'user_name' => $this->user->name,
            'type' => 'account_transfer',
            'category' => 'transfers',
            'color' => 'info'
        ];
    }
}
