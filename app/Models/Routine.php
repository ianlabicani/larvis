<?php

namespace App\Models;

use App\Enums\RoutineFrequency;
use App\Enums\RoutineStatus;
use App\Policies\RoutinePolicy;
use Database\Factories\RoutineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property RoutineStatus $status
 * @property RoutineFrequency $frequency
 * @property string $scheduled_time
 * @property string $timezone
 * @property Carbon $starts_on
 * @property Carbon|null $ends_on
 * @property Carbon|null $generation_cursor
 */
#[Fillable(['title', 'description', 'status', 'frequency', 'scheduled_time', 'timezone', 'starts_on', 'ends_on', 'generation_cursor'])]
#[UsePolicy(RoutinePolicy::class)]
class Routine extends Model
{
    /** @use HasFactory<RoutineFactory> */
    use HasFactory;

    protected $attributes = ['status' => 'active', 'frequency' => 'daily'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<RoutineOccurrence, $this> */
    public function occurrences(): HasMany
    {
        return $this->hasMany(RoutineOccurrence::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RoutineStatus::class, 'frequency' => RoutineFrequency::class, 'starts_on' => 'date', 'ends_on' => 'date', 'generation_cursor' => 'date'];
    }
}
