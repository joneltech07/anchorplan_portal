<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EodService;

class EodMarkLate extends Command
{
    protected $signature = 'eod:mark-late';
    protected $description = 'Mark missing EODs as late after cutoff';

    public function handle(EodService $service)
    {
        $service->checkLateSubmissions();
        $this->info('EOD late check complete');
        return 0;
    }
}
