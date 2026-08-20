export type TaskStatus = 'todo' | 'in_progress' | 'done' | 'cancelled';

export type TaskPriority = 'low' | 'medium' | 'high' | 'urgent';

export type Task = {
    id: number;
    title: string;
    description: string | null;
    status: TaskStatus;
    priority: TaskPriority;
    due_at: string | null;
    completed_at: string | null;
    is_overdue: boolean;
    is_due_soon: boolean;
    created_at: string;
    updated_at: string;
};

export type TaskSummary = {
    open: number;
    overdue: number;
    due_soon: number;
    completed: number;
};

export type CalendarEvent = {
    id: string;
    title: string;
    start: string;
    status: TaskStatus | 'pending' | 'completed' | 'missed' | 'paused';
    priority: TaskPriority | null;
    description: string | null;
    type: 'task' | 'routine';
    routine_id?: number;
    timezone?: string;
};
