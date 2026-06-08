<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EodService;

class EodManagerSummary extends Command
{
    protected $signature = 'eod:manager-summary';
    protected $description = 'Generate and send daily manager summaries for EODs';

    public function handle(EodService $service)
    {
        // placeholder: service to generate and notify managers
        $this->info('EOD manager summaries processed');
        return 0;
    }
}
