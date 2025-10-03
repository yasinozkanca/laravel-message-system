<?php

namespace App\Repositories\Contracts;

use App\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MessageRepositoryInterface
{
    /**
     * Get all pending messages.
     */
    public function getPendingMessages(int $limit = 2): Collection;

    /**
     * Get all sent messages with pagination.
     */
    public function getSentMessages(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a message by ID.
     */
    public function findById(int $id): ?Message;

    /**
     * Create a new message.
     */
    public function create(array $data): Message;

    /**
     * Update a message.
     */
    public function update(Message $message, array $data): bool;

    /**
     * Delete a message.
     */
    public function delete(Message $message): bool;

    /**
     * Get messages by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get messages count by status.
     */
    public function getCountByStatus(string $status): int;
}
