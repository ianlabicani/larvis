<?php

namespace App\Actions\Routines;

use App\Enums\RoutineOccurrenceStatus;
use App\Enums\RoutineStatus;
use App\Models\Routine;
use App\Models\RoutineOccurrence;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GenerateRoutineOccurrencesAction
{
    /** @return Collection<int, RoutineOccurrence> */
    public function handle(Routine $routine, ?CarbonInterface $now = null): Collection
    {
        if ($routine->status !== RoutineStatus::Active) {
            return new Collection;
        }

        $instant = CarbonImmutable::instance(($now ?? now())->toDateTime());
        $today = $instant->setTimezone($routine->timezone)->startOfDay();
        $startsOn = CarbonImmutable::createFromFormat('!Y-m-d', $routine->starts_on->toDateString(), $routine->timezone);
        $cursor = $routine->generation_cursor
            ? CarbonImmutable::createFromFormat('!Y-m-d', $routine->generation_cursor->toDateString(), $routine->timezone)
            : null;
        $endsOn = $routine->ends_on
            ? CarbonImmutable::createFromFormat('!Y-m-d', $routine->ends_on->toDateString(), $routine->timezone)
            : null;
        $firstDate = $cursor?->addDay() ?? $startsOn;
        $lastDate = $endsOn && $endsOn->lessThan($today) ? $endsOn : $today;

        if ($firstDate->greaterThan($lastDate)) {
            return new Collection;
        }

        return DB::transaction(function () use ($routine, $firstDate, $lastDate, $today): Collection {
            for ($date = $firstDate->toImmutable(); $date->lessThanOrEqualTo($lastDate); $date = $date->addDay()) {
                $scheduledFor = CarbonImmutable::createFromFormat(
                    'Y-m-d H:i:s',
                    $date->toDateString().' '.$this->normalizedTime($routine->scheduled_time),
                    $routine->timezone,
                );

                $routine->occurrences()->firstOrCreate(
                    ['local_date' => $date->toDateString()],
                    [
                        'scheduled_for' => $scheduledFor->utc(),
                        'status' => RoutineOccurrenceStatus::Pending,
                    ],
                );
            }

            $routine->occurrences()
                ->where('status', RoutineOccurrenceStatus::Pending)
                ->whereDate('local_date', '<', $today->toDateString())
                ->update(['status' => RoutineOccurrenceStatus::Missed->value]);

            $routine->forceFill(['generation_cursor' => $lastDate->toDateString()])->save();

            return $routine->occurrences()
                ->whereDate('local_date', '>=', $firstDate->toDateString())
                ->whereDate('local_date', '<=', $lastDate->toDateString())
                ->orderBy('local_date')
                ->get();
        });
    }

    private function normalizedTime(string $time): string
    {
        return mb_strlen($time) === 5 ? $time.':00' : $time;
    }
}
