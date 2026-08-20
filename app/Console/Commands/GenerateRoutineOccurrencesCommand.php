<?php

namespace App\Console\Commands;

use App\Actions\Routines\GenerateRoutineOccurrencesAction;
use App\Enums\RoutineStatus;
use App\Models\Routine;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Generate due daily routine occurrences')]
#[Signature('routines:generate-occurrences')]
class GenerateRoutineOccurrencesCommand extends Command
{
    public function handle(GenerateRoutineOccurrencesAction $generateOccurrences): int
    {
        Routine::query()->where('status', RoutineStatus::Active)->chunkById(100, function ($routines) use ($generateOccurrences): void {
            foreach ($routines as $routine) {
                $generateOccurrences->handle($routine);
            }
        });

        return self::SUCCESS;
    }
}
