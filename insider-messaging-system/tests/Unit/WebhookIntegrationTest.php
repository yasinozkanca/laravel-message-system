<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Services\MessageService;
use App\Repositories\Contracts\MessageLogRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\MessageCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class WebhookIntegrationTest extends TestCase
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

    public function test_webhook_request_format_matches_specification()
    {
        // Set up configuration
        config([
            'app.webhook_url' => 'https://webhook.site/test-url',
            'app.webhook_auth_key' => 'INS.me1x9uMcyYG1hKKQVPoc.b03j9aZwRTOCA2Ywo'
        ]);

        $message = new Message([
            'id' => 1,
            'content' => 'Insider - Project',
            'phone_number' => '+905551111111',
            'status' => 'pending'
        ]);

        // Mock the HTTP response
        Http::fake([
            'https://webhook.site/test-url' => Http::response([
                'message' => 'Accepted',
                'messageId' => '0732d23b-c629-4aca-b94f-ca6e9abb6cfd'
            ], 202)
        ]);

        $this->messageLogRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn(new \App\Models\MessageLog());

        $this->cacheService
            ->shouldReceive('cacheMessageData')
            ->once();

        // Execute the service
        $result = $this->messageService->sendMessage($message);

        // Assert the request was made with correct format
        Http::assertSent(function ($request) {
            return $request->url() === 'https://webhook.site/test-url' &&
                   $request->hasHeader('Content-Type', 'application/json') &&
                   $request->hasHeader('x-ins-auth-key', 'INS.me1x9uMcyYG1hKKQVPoc.b03j9aZwRTOCA2Ywo') &&
                   $request['to'] === '+905551111111' &&
                   $request['content'] === 'Insider - Project';
        });

        // Assert the response was handled correctly
        $this->assertTrue($result['success']);
        $this->assertEquals('0732d23b-c629-4aca-b94f-ca6e9abb6cfd', $result['message_id']);
    }

    public function test_webhook_handles_202_accepted_response()
    {
        config([
            'app.webhook_url' => 'https://webhook.site/test-url',
            'app.webhook_auth_key' => 'INS.me1x9uMcyYG1hKKQVPoc.b03j9aZwRTOCA2Ywo'
        ]);

        $message = new Message([
            'id' => 1,
            'content' => 'Test message',
            'phone_number' => '+905551111111',
            'status' => 'pending'
        ]);

        // Test with 202 Accepted response
        Http::fake([
            'https://webhook.site/test-url' => Http::response([
                'message' => 'Accepted',
                'messageId' => '67f2f8a8-ea58-4ed0-a6f9-ff217df4d849'
            ], 202)
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
        $this->assertEquals('67f2f8a8-ea58-4ed0-a6f9-ff217df4d849', $result['message_id']);
    }

    public function test_webhook_handles_failure_response()
    {
        config([
            'app.webhook_url' => 'https://webhook.site/test-url',
            'app.webhook_auth_key' => 'INS.me1x9uMcyYG1hKKQVPoc.b03j9aZwRTOCA2Ywo'
        ]);

        $message = new Message([
            'id' => 1,
            'content' => 'Test message',
            'phone_number' => '+905551111111',
            'status' => 'pending'
        ]);

        // Test with failure response
        Http::fake([
            'https://webhook.site/test-url' => Http::response([
                'error' => 'Invalid phone number'
            ], 400)
        ]);

        $this->messageLogRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn(new \App\Models\MessageLog());

        $result = $this->messageService->sendMessage($message);

        $this->assertFalse($result['success']);
    }

    public function test_webhook_works_without_auth_key()
    {
        config([
            'app.webhook_url' => 'https://webhook.site/test-url',
            'app.webhook_auth_key' => null
        ]);

        $message = new Message([
            'id' => 1,
            'content' => 'Test message',
            'phone_number' => '+905551111111',
            'status' => 'pending'
        ]);

        Http::fake([
            'https://webhook.site/test-url' => Http::response([
                'message' => 'Accepted',
                'messageId' => 'test-message-id'
            ], 202)
        ]);

        $this->messageLogRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn(new \App\Models\MessageLog());

        $this->cacheService
            ->shouldReceive('cacheMessageData')
            ->once();

        $result = $this->messageService->sendMessage($message);

        // Assert the request was made without auth header
        Http::assertSent(function ($request) {
            return $request->url() === 'https://webhook.site/test-url' &&
                   $request->hasHeader('Content-Type', 'application/json') &&
                   !$request->hasHeader('x-ins-auth-key');
        });

        $this->assertTrue($result['success']);
    }
}
