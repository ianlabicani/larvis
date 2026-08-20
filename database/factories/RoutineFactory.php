<?php

namespace Database\Factories;

use App\Enums\RoutineFrequency;
use App\Enums\RoutineStatus;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Routine>
 */
class RoutineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'status' => RoutineStatus::Active,
            'frequency' => RoutineFrequency::Daily,
            'scheduled_time' => '06:00:00',
            'timezone' => 'Asia/Manila',
            'starts_on' => today(),
            'ends_on' => null,
            'generation_cursor' => null,
        ];
    }
}
