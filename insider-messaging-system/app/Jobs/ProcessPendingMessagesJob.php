<?php

namespace App\Jobs;

use App\Services\Contracts\MessageServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPendingMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function handle(MessageServiceInterface $messageService): void
    {
        $messageService->processMessageSending();
    }
    public function failed(\Throwable $exception): void
    {
        Log::error('Process pending messages job failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
