<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some pending messages
        Message::factory()->count(10)->pending()->create();

        // Create some sent messages
        Message::factory()->count(5)->sent()->create();

        // Create some failed messages
        Message::factory()->count(2)->failed()->create();
    }
}
