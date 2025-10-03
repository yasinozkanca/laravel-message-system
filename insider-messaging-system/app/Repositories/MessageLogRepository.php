<?php

namespace App\Repositories;

use App\Models\Message;
use App\Models\MessageLog;
use App\Repositories\Contracts\MessageLogRepositoryInterface;

class MessageLogRepository implements MessageLogRepositoryInterface
{
    public function __construct(
        private MessageLog $model
    ) {}

    /**
     * Create a new message log.
     */
    public function create(array $data): MessageLog
    {
        return $this->model->create($data);
    }

    /**
     * Get logs for a specific message.
     */
    public function getByMessage(Message $message): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->where('message_id', $message->id)
            ->orderBy('sent_at', 'desc')
            ->get();
    }

    /**
     * Get the latest log for a message.
     */
    public function getLatestByMessage(Message $message): ?MessageLog
    {
        return $this->model
            ->where('message_id', $message->id)
            ->orderBy('sent_at', 'desc')
            ->first();
    }
}
