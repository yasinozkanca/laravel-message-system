<?php

namespace Tests\Feature;

use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_messages_list()
    {
        // Create test messages
        Message::factory()->create(['status' => 'sent']);
        Message::factory()->create(['status' => 'sent']);
        Message::factory()->create(['status' => 'pending']);

        $response = $this->getJson('/api/messages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'phone_number',
                        'status',
                        'message_id',
                        'sent_at',
                        'created_at',
                        'updated_at',
                        'logs'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to'
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ]
            ])
            ->assertJson([
                'success' => true
            ]);

        // Should only return sent messages
        $this->assertCount(2, $response->json('data'));
    }

    public function test_create_message_success()
    {
        $messageData = [
            'content' => 'Test message content',
            'phone_number' => '+1234567890'
        ];

        $response = $this->postJson('/api/messages', $messageData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Message created successfully'
            ]);

        // Check that the response has data
        $responseData = $response->json('data');
        $this->assertNotNull($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('content', $responseData);
        $this->assertArrayHasKey('phone_number', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        
        // Check the actual values
        $this->assertEquals('Test message content', $responseData['content']);
        $this->assertEquals('+1234567890', $responseData['phone_number']);
        $this->assertEquals('pending', $responseData['status']);

        $this->assertDatabaseHas('messages', $messageData);
    }

    public function test_create_message_validation_errors()
    {
        $response = $this->postJson('/api/messages', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'content',
                    'phone_number'
                ]
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed'
            ]);
    }

    public function test_create_message_content_too_long()
    {
        $messageData = [
            'content' => str_repeat('a', 161), // 161 characters
            'phone_number' => '+1234567890'
        ];

        $response = $this->postJson('/api/messages', $messageData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_get_messages_with_pagination()
    {
        // Create 25 sent messages
        Message::factory()->count(25)->create(['status' => 'sent']);

        $response = $this->getJson('/api/messages?per_page=10&page=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'current_page' => 2,
                    'per_page' => 10,
                    'total' => 25
                ]
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_get_messages_respects_per_page_limit()
    {
        Message::factory()->count(5)->create(['status' => 'sent']);

        $response = $this->getJson('/api/messages?per_page=150'); // Over the limit

        $response->assertStatus(200);
        $this->assertEquals(100, $response->json('pagination.per_page')); // Should be capped at 100
    }
}
