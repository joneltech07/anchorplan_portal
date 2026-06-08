<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EodService;

class EodSendReminders extends Command
{
    protected $signature = 'eod:send-reminders {--urgent}';
    protected $description = 'Send EOD reminders to users';

    public function handle(EodService $service)
    {
        $urgent = $this->option('urgent');
        $service->sendReminders($urgent);
        $this->info('EOD reminders sent');
        return 0;
    }
}
