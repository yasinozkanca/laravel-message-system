<?php

namespace App\Services\Contracts;

use App\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MessageServiceInterface
{
    /**
     * Get pending messages for sending.
     */
    public function getPendingMessages(int $limit = 2): Collection;

    /**
     * Get sent messages with pagination.
     */
    public function getSentMessages(int $perPage = 15): LengthAwarePaginator;

    /**
     * Send a message via webhook.
     */
    public function sendMessage(Message $message): array;

    /**
     * Process message sending with rate limiting.
     */
    public function processMessageSending(): void;

    /**
     * Create a new message.
     */
    public function createMessage(array $data): Message;

    /**
     * Validate message content.
     */
    public function validateMessageContent(string $content, int $limit = 160): bool;
}
