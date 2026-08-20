<?php

namespace App\Data;

final readonly class RoutineCalendarEventData
{
    public function __construct(public string $id, public int $routineId, public string $title, public string $start, public string $status, public ?string $description, public string $timezone) {}

    /** @return array{id: string, routine_id: int, title: string, start: string, status: string, description: string|null, timezone: string, type: string, priority: null} */
    public function toArray(): array
    {
        return ['id' => $this->id, 'routine_id' => $this->routineId, 'title' => $this->title, 'start' => $this->start, 'status' => $this->status, 'description' => $this->description, 'timezone' => $this->timezone, 'type' => 'routine', 'priority' => null];
    }
}
