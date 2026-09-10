<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_ticket(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/tickets', [
            'user_id' => $user->id,
            'title' => 'Payment failed',
            'description' => 'Customer payment was deducted but order failed.',
            'priority' => 'high',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Payment failed')
            ->assertJsonPath('data.priority', 'high');

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'title' => 'Payment failed',
            'priority' => 'high',
        ]);
    }

    public function test_ticket_creation_requires_required_fields(): void
    {
        $response = $this->postJson('/api/tickets', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'user_id',
                'title',
                'description',
                'priority',
            ]);
    }
    
    public function test_ticket_creation_rejects_invalid_priority(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/tickets', [
            'user_id' => $user->id,
            'title' => 'Payment failed',
            'description' => 'Payment issue',
            'priority' => 'super_urgent',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'priority',
            ]);
    }

    public function test_user_can_list_tickets(): void
    {
        $user = User::factory()->create();

        Ticket::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson('/api/tickets');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);

        $this->assertCount(3, $response->json('data'));
    }
}