<?php

namespace App\Enums;

enum RoutineOccurrenceStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Missed = 'missed';
}
