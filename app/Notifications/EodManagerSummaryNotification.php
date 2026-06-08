<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EodManagerSummaryNotification extends Notification
{
    use Queueable;

    public function __construct(public $summary = [])
    {
    }

    public function via($notifiable)
    {
        return ['mail','database'];
    }

    public function toMail($notifiable)
    {
        $m = (new MailMessage)->subject('EOD Daily Summary')->line('Daily EOD summary attached.');
        return $m;
    }

    public function toArray($notifiable)
    {
        return ['summary' => $this->summary];
    }
}
