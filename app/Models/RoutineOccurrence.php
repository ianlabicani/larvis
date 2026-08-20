<?php

namespace App\Models;

use App\Enums\RoutineOccurrenceStatus;
use Database\Factories\RoutineOccurrenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $routine_id
 * @property Carbon $local_date
 * @property Carbon $scheduled_for
 * @property RoutineOccurrenceStatus $status
 * @property Carbon|null $completed_at
 */
#[Fillable(['routine_id', 'local_date', 'scheduled_for', 'status', 'completed_at'])]
class RoutineOccurrence extends Model
{
    /** @use HasFactory<RoutineOccurrenceFactory> */
    use HasFactory;

    protected $attributes = ['status' => 'pending'];

    /** @return BelongsTo<Routine, $this> */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['local_date' => 'date', 'scheduled_for' => 'datetime', 'status' => RoutineOccurrenceStatus::class, 'completed_at' => 'datetime'];
    }
}
