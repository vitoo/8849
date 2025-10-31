<?php

namespace App\Console\Commands;

use App\Jobs\AyonSyncJob;
use Illuminate\Console\Command;

class SyncAyonCommand extends Command
{
    protected $signature = 'ayon:sync';
    protected $description = 'Synchronize talents with AYON';

    public function handle()
    {
        $this->info('Starting AYON synchronization...');
        
        AyonSyncJob::dispatch();
        
        $this->info('Synchronization job dispatched!');
    }
}