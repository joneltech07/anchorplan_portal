<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EodLateNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail','database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->subject('EOD Late Notice')->line('Your EOD was marked late.');
    }

    public function toArray($notifiable)
    {
        return ['message' => 'Your EOD was marked late'];
    }
}
