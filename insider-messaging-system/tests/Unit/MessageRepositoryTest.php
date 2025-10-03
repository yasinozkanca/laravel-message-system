<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Repositories\MessageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class MessageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MessageRepository $messageRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messageRepository = new MessageRepository(new Message());
    }

    public function test_create_message()
    {
        $data = [
            'content' => 'Test message',
            'phone_number' => '+1234567890',
            'status' => 'pending'
        ];

        $message = $this->messageRepository->create($data);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('Test message', $message->content);
        $this->assertEquals('+1234567890', $message->phone_number);
        $this->assertEquals('pending', $message->status);
        $this->assertDatabaseHas('messages', $data);
    }

    public function test_get_pending_messages()
    {
        // Create test messages
        Message::factory()->create(['status' => 'pending']);
        Message::factory()->create(['status' => 'pending']);
        Message::factory()->create(['status' => 'sent']);

        $pendingMessages = $this->messageRepository->getPendingMessages(2);

        $this->assertCount(2, $pendingMessages);
        $this->assertTrue($pendingMessages->every(fn($message) => $message->status === 'pending'));
    }

    public function test_get_sent_messages()
    {
        // Create test messages
        Message::factory()->create(['status' => 'sent']);
        Message::factory()->create(['status' => 'sent']);
        Message::factory()->create(['status' => 'pending']);

        $sentMessages = $this->messageRepository->getSentMessages(15);

        $this->assertInstanceOf(LengthAwarePaginator::class, $sentMessages);
        $this->assertEquals(2, $sentMessages->total());
    }

    public function test_find_by_id()
    {
        $message = Message::factory()->create();

        $foundMessage = $this->messageRepository->findById($message->id);

        $this->assertInstanceOf(Message::class, $foundMessage);
        $this->assertEquals($message->id, $foundMessage->id);
    }

    public function test_update_message()
    {
        $message = Message::factory()->create(['status' => 'pending']);

        $result = $this->messageRepository->update($message, ['status' => 'sent']);

        $this->assertTrue($result);
        $this->assertEquals('sent', $message->fresh()->status);
    }

    public function test_delete_message()
    {
        $message = Message::factory()->create();

        $result = $this->messageRepository->delete($message);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_get_by_status()
    {
        Message::factory()->create(['status' => 'pending']);
        Message::factory()->create(['status' => 'pending']);
        Message::factory()->create(['status' => 'sent']);

        $pendingMessages = $this->messageRepository->getByStatus('pending');

        $this->assertCount(2, $pendingMessages);
        $this->assertTrue($pendingMessages->every(fn($message) => $message->status === 'pending'));
    }

    public function test_get_count_by_status()
    {
        Message::factory()->create(['status' => 'pending']);
        Message::factory()->create(['status' => 'pending']);
        Message::factory()->create(['status' => 'sent']);

        $pendingCount = $this->messageRepository->getCountByStatus('pending');
        $sentCount = $this->messageRepository->getCountByStatus('sent');

        $this->assertEquals(2, $pendingCount);
        $this->assertEquals(1, $sentCount);
    }
}
