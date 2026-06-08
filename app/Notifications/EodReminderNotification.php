<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EodReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public $urgent = false)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $msg = (new MailMessage)
            ->subject('EOD Reminder')
            ->line('This is a reminder to submit your End of Day report.');

        if ($this->urgent) {
            $msg->line('This is an urgent reminder.');
        }

        return $msg;
    }

    public function toArray($notifiable)
    {
        return ['message' => 'Please submit your EOD'];
    }
}
