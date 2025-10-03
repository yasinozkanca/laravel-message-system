<?php

namespace App\Repositories\Contracts;

use App\Models\Message;
use App\Models\MessageLog;

interface MessageLogRepositoryInterface
{
    /**
     * Create a new message log.
     */
    public function create(array $data): MessageLog;

    /**
     * Get logs for a specific message.
     */
    public function getByMessage(Message $message): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get the latest log for a message.
     */
    public function getLatestByMessage(Message $message): ?MessageLog;
}
