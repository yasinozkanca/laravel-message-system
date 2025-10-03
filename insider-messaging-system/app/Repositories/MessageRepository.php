<?php

namespace App\Repositories;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    public function __construct(
        private Message $model
    ) {}

    /**
     * Get all pending messages.
     */
    public function getPendingMessages(int $limit = 2): Collection
    {
        return $this->model
            ->pending()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all sent messages with pagination.
     */
    public function getSentMessages(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->sent()
            ->with('logs')
            ->orderBy('sent_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a message by ID.
     */
    public function findById(int $id): ?Message
    {
        return $this->model->find($id);
    }

    /**
     * Create a new message.
     */
    public function create(array $data): Message
    {
        return $this->model->create($data);
    }

    /**
     * Update a message.
     */
    public function update(Message $message, array $data): bool
    {
        return $message->update($data);
    }

    /**
     * Delete a message.
     */
    public function delete(Message $message): bool
    {
        return $message->delete();
    }

    /**
     * Get messages by status.
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Get messages count by status.
     */
    public function getCountByStatus(string $status): int
    {
        return $this->model->where('status', $status)->count();
    }
}
