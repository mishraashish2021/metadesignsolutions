<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'title' => fake()->sentence(4),

            'description' => fake()->paragraph(),

            'priority' => fake()->randomElement(
                TicketPriority::cases()
            ),

            'status' => TicketStatus::OPEN,

            'due_date' => fake()->dateTimeBetween(
                'today',
                '+30 days'
            )->format('Y-m-d'),
        ];
    }
}