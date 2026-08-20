<?php

namespace App\Actions\Routines;

use App\Data\RoutineCalendarEventData;
use App\Enums\RoutineOccurrenceStatus;
use App\Enums\RoutineStatus;
use App\Models\Routine;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ListCalendarRoutineEventsAction
{
    /** @return Collection<int, RoutineCalendarEventData> */
    public function handle(User $user, CarbonInterface $start, CarbonInterface $end): Collection
    {
        Gate::forUser($user)->authorize('viewAny', Routine::class);

        return Routine::query()
            ->whereBelongsTo($user)
            ->with(['occurrences' => fn ($query) => $query->where('scheduled_for', '>=', $start)->where('scheduled_for', '<', $end)])
            ->get()
            ->flatMap(function (Routine $routine) use ($start, $end): array {
                $rangeStart = CarbonImmutable::instance($start->toDateTime())->setTimezone($routine->timezone)->startOfDay();
                $rangeEnd = CarbonImmutable::instance($end->toDateTime())->setTimezone($routine->timezone)->startOfDay();
                $startsOn = CarbonImmutable::createFromFormat('!Y-m-d', $routine->starts_on->toDateString(), $routine->timezone);
                $endsOn = $routine->ends_on ? CarbonImmutable::createFromFormat('!Y-m-d', $routine->ends_on->toDateString(), $routine->timezone) : null;
                $date = $rangeStart->greaterThan($startsOn) ? $rangeStart : $startsOn;
                $last = $endsOn && $endsOn->lessThan($rangeEnd) ? $endsOn : $rangeEnd->subDay();
                $stored = $routine->occurrences->keyBy(fn ($occurrence) => $occurrence->local_date->toDateString());
                $events = [];

                for (; $date->lessThanOrEqualTo($last); $date = $date->addDay()) {
                    $time = mb_strlen($routine->scheduled_time) === 5 ? $routine->scheduled_time.':00' : $routine->scheduled_time;
                    $scheduled = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $date->toDateString().' '.$time, $routine->timezone)->utc();
                    if ($scheduled->lessThan($start) || $scheduled->greaterThanOrEqualTo($end)) {
                        continue;
                    }
                    $occurrence = $stored->get($date->toDateString());
                    $status = $occurrence?->status->value ?? ($routine->status === RoutineStatus::Paused ? RoutineStatus::Paused->value : RoutineOccurrenceStatus::Pending->value);
                    $events[] = new RoutineCalendarEventData(
                        'routine-'.$routine->id.'-'.$date->toDateString(),
                        $routine->id,
                        $routine->title,
                        $scheduled->toISOString(),
                        $status,
                        $routine->description,
                        $routine->timezone,
                    );
                }

                return $events;
            })
            ->sortBy(fn (RoutineCalendarEventData $event) => $event->start)
            ->values();
    }
}
