<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPendingMessagesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMessagesCommand extends Command
{
    protected $signature = 'messages:send 
                            {--continuous : Run continuously with 5-second intervals}';

    protected $description = 'Send all pending messages to recipients via webhook with rate limiting';
    public function handle(): int
    {
        $continuous = $this->option('continuous');

        if ($continuous) {
            $this->info('Running in continuous mode. Press Ctrl+C to stop.');
            
            while (true) {
                $this->processMessages();
                sleep(5);
            }
        } else {
            $this->processMessages();
        }

        return Command::SUCCESS;
    }

    private function processMessages(): void
    {
        $this->info('Processing all pending messages...');
        ProcessPendingMessagesJob::dispatch();
    }
}
