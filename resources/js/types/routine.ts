export type RoutineStatus = 'active' | 'paused';
export type RoutineOccurrenceStatus = 'pending' | 'completed' | 'missed';

export type RoutineOccurrence = {
    id: number;
    local_date: string;
    scheduled_for: string;
    status: RoutineOccurrenceStatus;
    completed_at: string | null;
};

export type Routine = {
    id: number;
    title: string;
    description: string | null;
    status: RoutineStatus;
    frequency: 'daily';
    scheduled_time: string;
    timezone: string;
    starts_on: string;
    ends_on: string | null;
    next_occurrence_at: string | null;
    occurrences: RoutineOccurrence[];
};

export type TodayRoutineOccurrence = RoutineOccurrence & {
    routine: Routine;
};
