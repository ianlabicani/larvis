<?php

namespace App\Data;

use App\Enums\RoutineOccurrenceStatus;
use App\Enums\RoutineStatus;
use App\Models\Routine;
use App\Models\RoutineOccurrence;
use Carbon\CarbonImmutable;

final readonly class RoutineData
{
    /** @param list<array{id: int, local_date: string, scheduled_for: string, status: string, completed_at: string|null}> $occurrences */
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public string $status,
        public string $frequency,
        public string $scheduledTime,
        public string $timezone,
        public string $startsOn,
        public ?string $endsOn,
        public ?string $nextOccurrenceAt,
        public array $occurrences,
    ) {}

    public static function fromModel(Routine $routine): self
    {
        $routine->loadMissing('occurrences');
        $pending = $routine->occurrences->first(fn (RoutineOccurrence $occurrence) => $occurrence->status === RoutineOccurrenceStatus::Pending);
        $next = $pending?->scheduled_for;

        if ($next === null && $routine->status === RoutineStatus::Active) {
            $date = CarbonImmutable::now($routine->timezone)->startOfDay()->addDay();
            if ($date->lessThan($routine->starts_on)) {
                $date = $routine->starts_on->toImmutable();
            }
            if ($routine->ends_on === null || $date->lessThanOrEqualTo($routine->ends_on)) {
                $time = mb_strlen($routine->scheduled_time) === 5 ? $routine->scheduled_time.':00' : $routine->scheduled_time;
                $next = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $date->toDateString().' '.$time, $routine->timezone)->utc();
            }
        }

        return new self(
            $routine->id,
            $routine->title,
            $routine->description,
            $routine->status->value,
            $routine->frequency->value,
            $routine->scheduled_time,
            $routine->timezone,
            $routine->starts_on->toDateString(),
            $routine->ends_on?->toDateString(),
            $next?->toISOString(),
            array_values($routine->occurrences->map(fn (RoutineOccurrence $occurrence) => RoutineOccurrenceData::fromModel($occurrence)->toArray())->all()),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id, 'title' => $this->title, 'description' => $this->description,
            'status' => $this->status, 'frequency' => $this->frequency, 'scheduled_time' => $this->scheduledTime,
            'timezone' => $this->timezone, 'starts_on' => $this->startsOn, 'ends_on' => $this->endsOn,
            'next_occurrence_at' => $this->nextOccurrenceAt, 'occurrences' => $this->occurrences,
        ];
    }
}
