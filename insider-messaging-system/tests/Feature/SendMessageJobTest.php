<?php

namespace Tests\Feature;

use App\Jobs\SendMessageJob;
use App\Models\Message;
use App\Services\Contracts\MessageServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class SendMessageJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_send_message_job_can_be_dispatched()
    {
        $message = Message::factory()->create(['status' => 'pending']);

        SendMessageJob::dispatch($message);

        Queue::assertPushed(SendMessageJob::class, function ($job) use ($message) {
            return $job->message->id === $message->id;
        });
    }

    public function test_send_message_job_handles_success()
    {
        Http::fake([
            '*' => Http::response([
                'message' => 'Accepted',
                'messageId' => '0732d23b-c629-4aca-b94f-ca6e9abb6cfd'
            ], 202)
        ]);

        $message = Message::factory()->create([
            'status' => 'pending',
            'content' => 'Test message',
            'phone_number' => '+1234567890'
        ]);

        $messageService = Mockery::mock(MessageServiceInterface::class);
        $messageService->shouldReceive('sendMessage')
            ->with($message)
            ->once()
            ->andReturnUsing(function ($message) {
                // Simulate the service updating the message
                $message->markAsSent('0732d23b-c629-4aca-b94f-ca6e9abb6cfd');
                return [
                    'success' => true,
                    'message_id' => '0732d23b-c629-4aca-b94f-ca6e9abb6cfd'
                ];
            });

        $this->app->instance(MessageServiceInterface::class, $messageService);

        $job = new SendMessageJob($message);
        $job->handle($messageService);

        $this->assertEquals('sent', $message->fresh()->status);
        $this->assertEquals('0732d23b-c629-4aca-b94f-ca6e9abb6cfd', $message->fresh()->message_id);
    }

    public function test_send_message_job_handles_failure()
    {
        Http::fake([
            '*' => Http::response([], 500)
        ]);

        $message = Message::factory()->create([
            'status' => 'pending',
            'content' => 'Test message',
            'phone_number' => '+1234567890'
        ]);

        $messageService = Mockery::mock(MessageServiceInterface::class);
        $messageService->shouldReceive('sendMessage')
            ->with($message)
            ->once()
            ->andReturnUsing(function ($message) {
                // Simulate the service marking the message as failed
                $message->markAsFailed();
                return [
                    'success' => false,
                    'error' => 'HTTP request failed'
                ];
            });

        $this->app->instance(MessageServiceInterface::class, $messageService);

        $job = new SendMessageJob($message);
        
        try {
            $job->handle($messageService);
        } catch (\Exception $e) {
            // Expected to throw exception on failure
        }

        $this->assertEquals('failed', $message->fresh()->status);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
