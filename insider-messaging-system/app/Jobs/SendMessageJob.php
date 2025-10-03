<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\Contracts\MessageServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public Message $message
    ) {}

    public function handle(MessageServiceInterface $messageService): void
    {
        $result = $messageService->sendMessage($this->message);

        if (!$result['success']) {
            throw new \Exception($result['error'] ?? 'Failed to send message');
        }
    }
    public function failed(\Throwable $exception): void
    {
        Log::error('Message job failed', [
            'message_id' => $this->message->id,
            'error' => $exception->getMessage(),
        ]);

        $this->message->markAsFailed();
    }
}
