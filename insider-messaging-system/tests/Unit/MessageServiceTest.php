<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Repositories\Contracts\MessageLogRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\MessageCacheService;
use App\Services\MessageService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    private MessageService $messageService;
    private $messageRepository;
    private $messageLogRepository;
    private $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageRepository = Mockery::mock(MessageRepositoryInterface::class);
        $this->messageLogRepository = Mockery::mock(MessageLogRepositoryInterface::class);
        $this->cacheService = Mockery::mock(MessageCacheService::class);

        $this->messageService = new MessageService(
            $this->messageRepository,
            $this->messageLogRepository,
            $this->cacheService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_pending_messages()
    {
        $messages = new Collection([
            new Message(['content' => 'Test message 1', 'phone_number' => '+1234567890']),
            new Message(['content' => 'Test message 2', 'phone_number' => '+1234567891']),
        ]);

        $this->messageRepository
            ->shouldReceive('getPendingMessages')
            ->with(2)
            ->once()
            ->andReturn($messages);

        $result = $this->messageService->getPendingMessages(2);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_validate_message_content_within_limit()
    {
        $content = 'This is a test message within the 160 character limit.';
        
        $result = $this->messageService->validateMessageContent($content);
        
        $this->assertTrue($result);
    }

    public function test_validate_message_content_exceeds_limit()
    {
        $content = str_repeat('a', 161); // 161 characters
        
        $result = $this->messageService->validateMessageContent($content);
        
        $this->assertFalse($result);
    }

    public function test_send_message_success()
    {
        Http::fake([
            '*' => Http::response([
                'message' => 'Accepted',
                'messageId' => '0732d23b-c629-4aca-b94f-ca6e9abb6cfd'
            ], 202)
        ]);

        $message = new Message([
            'id' => 1,
            'content' => 'Test message',
            'phone_number' => '+1234567890',
            'status' => 'pending'
        ]);

        $this->messageLogRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn(new \App\Models\MessageLog());

        $this->cacheService
            ->shouldReceive('cacheMessageData')
            ->once();

        $result = $this->messageService->sendMessage($message);

        $this->assertTrue($result['success']);
        $this->assertEquals('0732d23b-c629-4aca-b94f-ca6e9abb6cfd', $result['message_id']);
    }

    public function test_send_message_failure()
    {
        Http::fake([
            '*' => Http::response([], 500)
        ]);

        $message = new Message([
            'id' => 1,
            'content' => 'Test message',
            'phone_number' => '+1234567890',
            'status' => 'pending'
        ]);

        $this->messageLogRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn(new \App\Models\MessageLog());

        $result = $this->messageService->sendMessage($message);

        $this->assertFalse($result['success']);
    }

    public function test_create_message_success()
    {
        $data = [
            'content' => 'Test message',
            'phone_number' => '+1234567890'
        ];

        $message = new Message($data);

        $this->messageRepository
            ->shouldReceive('create')
            ->with($data)
            ->once()
            ->andReturn($message);

        $result = $this->messageService->createMessage($data);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertEquals('Test message', $result->content);
    }

    public function test_create_message_exceeds_character_limit()
    {
        $data = [
            'content' => str_repeat('a', 161), // 161 characters
            'phone_number' => '+1234567890'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Message content exceeds character limit');

        $this->messageService->createMessage($data);
    }
}
