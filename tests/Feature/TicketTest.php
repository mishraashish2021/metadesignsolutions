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

    public function test_tickets_can_be_filtered_by_priority(): void
    {
        $user = User::factory()->create();

        Ticket::factory()->create([
            'user_id' => $user->id,
            'priority' => 'high',
        ]);

        Ticket::factory()->create([
            'user_id' => $user->id,
            'priority' => 'low',
        ]);

        $response = $this->getJson('/api/tickets?priority=high');

        $response->assertOk();

        $this->assertCount(1, $response->json('data'));

        $this->assertEquals(
            'high',
            $response->json('data.0.priority')
        );
    }

    public function test_user_can_view_a_single_ticket(): void
{
    $user = User::factory()->create();

    $ticket = Ticket::factory()->create([
        'user_id' => $user->id,
        'title' => 'Payment issue',
    ]);

    $response = $this->getJson(
        "/api/tickets/{$ticket->id}"
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.id',
            $ticket->id
        )
        ->assertJsonPath(
            'data.title',
            'Payment issue'
        );
}

public function test_user_can_update_a_ticket(): void
{
    $user = User::factory()->create();

    $ticket = Ticket::factory()->create([
        'user_id' => $user->id,
        'priority' => 'low',
    ]);

    $response = $this->putJson(
        "/api/tickets/{$ticket->id}",
        [
            'title' => 'Updated payment issue',
            'priority' => 'urgent',
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.title',
            'Updated payment issue'
        )
        ->assertJsonPath(
            'data.priority',
            'urgent'
        );

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'title' => 'Updated payment issue',
        'priority' => 'urgent',
    ]);
}

public function test_user_can_delete_a_ticket(): void
{
    $user = User::factory()->create();

    $ticket = Ticket::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->deleteJson(
        "/api/tickets/{$ticket->id}"
    );

    $response->assertNoContent();

    $this->assertDatabaseMissing('tickets', [
        'id' => $ticket->id,
    ]);
}

public function test_viewing_non_existing_ticket_returns_404(): void
{
    $response = $this->getJson('/api/tickets/99999');

    $response->assertNotFound();
}

public function test_deleting_non_existing_ticket_returns_404(): void
{
    $response = $this->deleteJson('/api/tickets/99999');

    $response->assertNotFound();
}
}