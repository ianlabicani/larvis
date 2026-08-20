<?php

namespace App\Data;

use App\Models\Task;

class CalendarEventData
{
    public function __construct(
        public string $id,
        public string $title,
        public string $start,
        public string $status,
        public string $priority,
        public ?string $description,
    ) {}

    public static function fromModel(Task $task): self
    {
        return new self(
            id: (string) $task->id,
            title: $task->title,
            start: $task->due_at?->toISOString() ?? '',
            status: $task->status->value,
            priority: $task->priority->value,
            description: $task->description,
        );
    }

    /**
     * @return array{id: string, title: string, start: string, status: string, priority: string, description: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start,
            'status' => $this->status,
            'priority' => $this->priority,
            'description' => $this->description,
        ];
    }
}
