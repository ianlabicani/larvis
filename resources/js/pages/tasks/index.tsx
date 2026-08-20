import { Head, Link, router } from '@inertiajs/react';
import { Check, Pencil, Plus, RotateCcw, Trash2 } from 'lucide-react';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { create, edit, index } from '@/routes/tasks';
import type { Task } from '@/types';

type Filters = {
    status: string | null;
    priority: string | null;
};

function label(value: string): string {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function badgeVariant(task: Task): 'default' | 'destructive' | 'outline' | 'secondary' {
    if (task.is_overdue) {
        return 'destructive';
    }

    if (task.status === 'done') {
        return 'secondary';
    }

    return task.priority === 'urgent' ? 'destructive' : 'outline';
}

export default function TasksIndex({
    tasks,
    filters,
    priorities,
    statuses,
}: {
    tasks: Task[];
    filters: Filters;
    priorities: string[];
    statuses: string[];
}) {
    function updateFilters(partialFilters: Partial<Filters>): void {
        const nextFilters = { ...filters, ...partialFilters };

        router.get(
            index({
                query: {
                    status: nextFilters.status,
                    priority: nextFilters.priority,
                },
            }),
            {},
            { preserveScroll: true },
        );
    }

    return (
        <>
            <Head title="Tasks" />

            <div className="space-y-6">
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <Heading
                        variant="small"
                        title="Tasks"
                        description="Keep your priorities clear and your work moving."
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Create task
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-3 sm:grid-cols-2">
                    <select
                        aria-label="Filter by status"
                        value={filters.status ?? ''}
                        onChange={(event) => updateFilters({ status: event.target.value || null })}
                        className="border-input bg-background dark:bg-input/30 h-9 rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                    >
                        <option value="">All statuses</option>
                        {statuses.map((status) => (
                            <option key={status} value={status}>
                                {label(status)}
                            </option>
                        ))}
                    </select>
                    <select
                        aria-label="Filter by priority"
                        value={filters.priority ?? ''}
                        onChange={(event) => updateFilters({ priority: event.target.value || null })}
                        className="border-input bg-background dark:bg-input/30 h-9 rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                    >
                        <option value="">All priorities</option>
                        {priorities.map((priority) => (
                            <option key={priority} value={priority}>
                                {label(priority)}
                            </option>
                        ))}
                    </select>
                </div>

                {tasks.length === 0 ? (
                    <Card>
                        <CardContent className="py-12 text-center text-sm text-muted-foreground">
                            No tasks match these filters.
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-3">
                        {tasks.map((task) => (
                            <Card key={task.id}>
                                <CardContent className="flex flex-col gap-4 pt-6 sm:flex-row sm:items-center sm:justify-between">
                                    <div className="space-y-2">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <h2 className="font-medium">{task.title}</h2>
                                            <Badge variant={badgeVariant(task)}>{label(task.status)}</Badge>
                                            <Badge variant="outline">{label(task.priority)}</Badge>
                                            {task.is_due_soon && !task.is_overdue && (
                                                <Badge variant="secondary">Due soon</Badge>
                                            )}
                                        </div>
                                        {task.description && (
                                            <p className="text-sm text-muted-foreground">{task.description}</p>
                                        )}
                                        {task.due_at && (
                                            <p className={task.is_overdue ? 'text-sm text-destructive' : 'text-sm text-muted-foreground'}>
                                                Due {new Date(task.due_at).toLocaleString()}
                                            </p>
                                        )}
                                    </div>

                                    <div className="flex flex-wrap gap-2">
                                        {task.status === 'done' ? (
                                            <Button size="sm" variant="outline" asChild>
                                                <Link href={TaskController.reopen(task.id)} method="patch" as="button">
                                                    <RotateCcw />
                                                    Reopen
                                                </Link>
                                            </Button>
                                        ) : (
                                            <Button size="sm" variant="outline" asChild>
                                                <Link href={TaskController.complete(task.id)} method="patch" as="button">
                                                    <Check />
                                                    Complete
                                                </Link>
                                            </Button>
                                        )}
                                        <Button size="sm" variant="outline" asChild>
                                            <Link href={edit(task.id)}>
                                                <Pencil />
                                                Edit
                                            </Link>
                                        </Button>
                                        <Button size="sm" variant="destructive" asChild>
                                            <Link
                                                href={TaskController.destroy(task.id)}
                                                method="delete"
                                                as="button"
                                                onClick={(event) => {
                                                    if (! window.confirm(`Delete “${task.title}”?`)) {
                                                        event.preventDefault();
                                                    }
                                                }}
                                            >
                                                <Trash2 />
                                                Delete
                                            </Link>
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

TasksIndex.layout = {
    breadcrumbs: [
        {
            title: 'Tasks',
            href: index(),
        },
    ],
};
