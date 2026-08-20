<?php

namespace App\Data;

use App\Models\Task;

final readonly class TaskDeletionData
{
    public function __construct(
        public Task $task,
        public bool $alreadyDeleted,
    ) {}

    /**
     * @return array{task: array{id: int, title: string, deleted_at: string}, already_deleted: bool}
     */
    public function toArray(): array
    {
        return [
            'task' => [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'deleted_at' => $this->task->deleted_at?->toISOString() ?? '',
            ],
            'already_deleted' => $this->alreadyDeleted,
        ];
    }
}
