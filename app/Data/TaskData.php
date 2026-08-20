<?php

namespace App\Data;

use App\Models\Task;

final readonly class TaskData
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public string $status,
        public string $priority,
        public ?string $dueAt,
        public ?string $completedAt,
        public bool $isOverdue,
        public bool $isDueSoon,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(Task $task): self
    {
        $isOpen = $task->status->isOpen();

        return new self(
            id: $task->id,
            title: $task->title,
            description: $task->description,
            status: $task->status->value,
            priority: $task->priority->value,
            dueAt: $task->due_at?->toISOString(),
            completedAt: $task->completed_at?->toISOString(),
            isOverdue: $isOpen && $task->due_at?->isPast() === true,
            isDueSoon: $isOpen && $task->due_at?->isBetween(now(), now()->addDays(7)) === true,
            createdAt: $task->created_at?->toISOString() ?? '',
            updatedAt: $task->updated_at?->toISOString() ?? '',
        );
    }

    /**
     * @return array<string, bool|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_at' => $this->dueAt,
            'completed_at' => $this->completedAt,
            'is_overdue' => $this->isOverdue,
            'is_due_soon' => $this->isDueSoon,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
