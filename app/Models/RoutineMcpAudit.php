<?php

namespace App\Models;

use Database\Factories\RoutineMcpAuditFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'routine_id', 'routine_occurrence_id', 'tool_name', 'input', 'result', 'correlation_id'])]
class RoutineMcpAudit extends Model
{
    /** @use HasFactory<RoutineMcpAuditFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Routine, $this> */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    /** @return BelongsTo<RoutineOccurrence, $this> */
    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(RoutineOccurrence::class, 'routine_occurrence_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['input' => 'array', 'result' => 'array'];
    }
}
