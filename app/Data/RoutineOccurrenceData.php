<?php

namespace App\Data;

use App\Models\RoutineOccurrence;

final readonly class RoutineOccurrenceData
{
    public function __construct(public int $id, public string $localDate, public string $scheduledFor, public string $status, public ?string $completedAt) {}

    public static function fromModel(RoutineOccurrence $occurrence): self
    {
        return new self($occurrence->id, $occurrence->local_date->toDateString(), $occurrence->scheduled_for->toISOString(), $occurrence->status->value, $occurrence->completed_at?->toISOString());
    }

    /** @return array{id: int, local_date: string, scheduled_for: string, status: string, completed_at: string|null} */
    public function toArray(): array
    {
        return ['id' => $this->id, 'local_date' => $this->localDate, 'scheduled_for' => $this->scheduledFor, 'status' => $this->status, 'completed_at' => $this->completedAt];
    }
}
