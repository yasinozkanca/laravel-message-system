<?php

namespace App\Services;

use App\Models\Message;
use App\Repositories\Contracts\MessageLogRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\Contracts\MessageServiceInterface;
use App\Services\MessageCacheService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessageService implements MessageServiceInterface
{
    public function __construct(
        private MessageRepositoryInterface $messageRepository,
        private MessageLogRepositoryInterface $messageLogRepository,
        private MessageCacheService $cacheService
    ) {}

    public function getPendingMessages(int $limit = 2): Collection
    {
        return $this->messageRepository->getPendingMessages($limit);
    }

    public function getSentMessages(int $perPage = 15): LengthAwarePaginator
    {
        return $this->messageRepository->getSentMessages($perPage);
    }
    public function sendMessage(Message $message): array
    {
        try {
            $webhookUrl = config('app.webhook_url');
            $authKey = config('app.webhook_auth_key');
            
            if (!$webhookUrl) {
                throw new \Exception('Webhook URL not configured');
            }

            $payload = [
                'to' => $message->phone_number,
                'content' => $message->content,
            ];

            $headers = [
                'Content-Type' => 'application/json',
            ];

            if ($authKey) {
                $headers['x-ins-auth-key'] = $authKey;
            }

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($webhookUrl, $payload);

            $responseData = $response->json();
            
            if ($responseData === null) {
                $responseData = [];
            }
            
            $status = 'failed';
            if ($response->successful()) {
                $status = 'sent';
                
                $messageId = null;
                
                if (isset($responseData['messageId'])) {
                    $messageId = $responseData['messageId'];
                }
                elseif ($response->header('X-Request-ID')) {
                    $messageId = $response->header('X-Request-ID');
                }
                elseif (isset($responseData['id'])) {
                    $messageId = $responseData['id'];
                }
                
                if ($messageId) {
                    $responseData['messageId'] = $messageId;
                }
                
                Log::info('Message sent successfully', [
                    'message_id' => $message->id,
                    'external_message_id' => $messageId,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::warning('Message sending failed', [
                    'message_id' => $message->id,
                    'status_code' => $response->status()
                ]);
            }

            $this->messageLogRepository->create([
                'message_id' => $message->id,
                'external_message_id' => $responseData['messageId'] ?? null,
                'status' => $status,
                'response' => $responseData,
                'sent_at' => now(),
            ]);

            if ($status === 'sent') {
                $message->markAsSent($responseData['messageId'] ?? null);
                
                if ($responseData && is_array($responseData)) {
                    $this->cacheService->cacheMessageData($message, $responseData);
                }
            } else {
                $message->markAsFailed();
            }

            return [
                'success' => $status === 'sent',
                'message_id' => $responseData['messageId'] ?? null,
                'response' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send message', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            $this->messageLogRepository->create([
                'message_id' => $message->id,
                'status' => 'failed',
                'response' => ['error' => $e->getMessage()],
                'sent_at' => now(),
            ]);

            $message->markAsFailed();

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function processMessageSending(): void
    {
        $pendingMessages = $this->getPendingMessages(2);

        foreach ($pendingMessages as $message) {
            try {
                $result = $this->sendMessage($message);
                
                if (!$result['success']) {
                    Log::warning('Message sending failed', [
                        'message_id' => $message->id,
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);
                }
                
            } catch (\Exception $e) {
                Log::error('Exception during message sending', [
                    'message_id' => $message->id,
                    'error' => $e->getMessage()
                ]);
                
                $message->markAsFailed();
            }
            
            sleep(2.5);
        }
    }

    public function createMessage(array $data): Message
    {
        if (!$this->validateMessageContent($data['content'])) {
            throw new \InvalidArgumentException('Message content exceeds character limit');
        }

        return $this->messageRepository->create($data);
    }

    public function validateMessageContent(string $content, int $limit = 160): bool
    {
        return strlen($content) <= $limit;
    }

}
