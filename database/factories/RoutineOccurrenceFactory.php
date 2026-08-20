<?php

namespace Database\Factories;

use App\Enums\RoutineOccurrenceStatus;
use App\Models\Routine;
use App\Models\RoutineOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutineOccurrence>
 */
class RoutineOccurrenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'routine_id' => Routine::factory(),
            'local_date' => today(),
            'scheduled_for' => now(),
            'status' => RoutineOccurrenceStatus::Pending,
            'completed_at' => null,
        ];
    }
}
