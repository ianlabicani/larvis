<?php

namespace Database\Factories;

use App\Models\Routine;
use App\Models\RoutineMcpAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutineMcpAudit>
 */
class RoutineMcpAuditFactory extends Factory
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
            'routine_id' => Routine::factory(),
            'routine_occurrence_id' => null,
            'tool_name' => 'routines-create',
            'input' => [],
            'result' => [],
            'correlation_id' => fake()->uuid(),
        ];
    }
}
