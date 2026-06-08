<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EodMissingEscalation extends Notification
{
    use Queueable;

    public function __construct(public $userName = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail','database'];
    }

    public function toMail($notifiable)
    {
        $m = (new MailMessage)->subject('EOD Missing Escalation')->line('A member has missing EOD reports.');
        if ($this->userName) {
            $m->line('User: '.$this->userName);
        }
        return $m;
    }

    public function toArray($notifiable)
    {
        return ['message' => 'EOD missing escalation', 'user' => $this->userName];
    }
}
