<?php

namespace App\Services;

use App\Models\Message;
use Illuminate\Support\Facades\Cache;

class MessageCacheService
{
    private const CACHE_PREFIX = 'message:';
    private const CACHE_TTL = 86400;

    public function cacheMessageData(Message $message, array $responseData): void
    {
        $cacheKey = $this->getCacheKey($message->id);
        
        $cacheData = [
            'message_id' => $responseData['messageId'] ?? null,
            'sent_at' => now()->toISOString(),
            'phone_number' => $message->phone_number,
            'content' => $message->content,
            'status' => 'sent',
        ];

        Cache::put($cacheKey, $cacheData, self::CACHE_TTL);
    }

    public function getCachedMessageData(int $messageId): ?array
    {
        $cacheKey = $this->getCacheKey($messageId);
        
        return Cache::get($cacheKey);
    }

    public function isMessageCached(int $messageId): bool
    {
        $cacheKey = $this->getCacheKey($messageId);
        
        return Cache::has($cacheKey);
    }

    public function removeCachedMessageData(int $messageId): bool
    {
        $cacheKey = $this->getCacheKey($messageId);
        
        return Cache::forget($cacheKey);
    }

    public function getAllCachedMessageIds(): array
    {
        $pattern = self::CACHE_PREFIX . '*';
        
        $keys = Cache::getRedis()->keys($pattern);
        
        return array_map(function ($key) {
            return str_replace(self::CACHE_PREFIX, '', $key);
        }, $keys);
    }

    public function getCacheStats(): array
    {
        $cachedIds = $this->getAllCachedMessageIds();
        
        return [
            'total_cached_messages' => count($cachedIds),
            'cache_ttl' => self::CACHE_TTL,
            'cache_prefix' => self::CACHE_PREFIX,
        ];
    }

    private function getCacheKey(int $messageId): string
    {
        return self::CACHE_PREFIX . $messageId;
    }
}
