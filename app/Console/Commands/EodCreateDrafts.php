<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EodService;

class EodCreateDrafts extends Command
{
    protected $signature = 'eod:create-drafts';
    protected $description = 'Generate daily EOD drafts for users who require them';

    public function handle(EodService $service)
    {
        $service->generateDraftsForToday();
        $this->info('EOD drafts generated');
        return 0;
    }
}
