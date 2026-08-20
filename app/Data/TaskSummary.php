<?php

namespace App\Data;

final readonly class TaskSummary
{
    public function __construct(
        public int $open,
        public int $overdue,
        public int $dueSoon,
        public int $completed,
    ) {}

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'open' => $this->open,
            'overdue' => $this->overdue,
            'due_soon' => $this->dueSoon,
            'completed' => $this->completed,
        ];
    }
}
