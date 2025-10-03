<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPendingMessagesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMessagesCommand extends Command
{
    protected $signature = 'messages:send 
                            {--limit=2 : Number of messages to process in this batch}
                            {--continuous : Run continuously with 5-second intervals}';

    protected $description = 'Send pending messages to recipients via webhook';
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $continuous = $this->option('continuous');

        if ($continuous) {
            $this->info('Running in continuous mode. Press Ctrl+C to stop.');
            
            while (true) {
                $this->processMessages($limit);
                sleep(5);
            }
        } else {
            $this->processMessages($limit);
        }

        return Command::SUCCESS;
    }

    private function processMessages(int $limit): void
    {
        ProcessPendingMessagesJob::dispatch();
    }
}
