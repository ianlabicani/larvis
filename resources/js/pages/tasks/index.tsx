import { Head, Link, router } from '@inertiajs/react';
import { Check, Pencil, Plus, RotateCcw, Trash2 } from 'lucide-react';
import { useState } from 'react';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import Heading from '@/components/heading';
import TaskCalendar from '@/components/task-calendar';
import TaskCalendarDialog from '@/components/task-calendar-dialog';
import type { TaskEditor } from '@/components/task-calendar-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { index } from '@/routes/tasks';
import type { Task } from '@/types';

type Filters = {
    status: string | null;
    priority: string | null;
};

function label(value: string): string {
    return value
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function badgeVariant(
    task: Task,
): 'default' | 'destructive' | 'outline' | 'secondary' {
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
    timezone,
}: {
    tasks: Task[];
    filters: Filters;
    priorities: string[];
    statuses: string[];
    timezone: string;
}) {
    const [view, setView] = useState<'list' | 'calendar'>('calendar');
    const [editor, setEditor] = useState<TaskEditor | null>(null);
    const [calendarRefreshKey, setCalendarRefreshKey] = useState(0);

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
                    <div className="flex flex-wrap gap-2">
                        <Button
                            variant={
                                view === 'calendar' ? 'default' : 'outline'
                            }
                            onClick={() => setView('calendar')}
                        >
                            Calendar
                        </Button>
                        <Button
                            variant={view === 'list' ? 'default' : 'outline'}
                            onClick={() => setView('list')}
                        >
                            List
                        </Button>
                        <Button onClick={() => setEditor({ mode: 'create' })}>
                            <Plus />
                            New task
                        </Button>
                    </div>
                </div>

                {view === 'calendar' ? (
                    <TaskCalendar
                        key={calendarRefreshKey}
                        timezone={timezone}
                        onCreate={(dueAt) =>
                            setEditor({ mode: 'create', dueAt })
                        }
                        onEdit={(task) => setEditor({ mode: 'edit', task })}
                    />
                ) : (
                    <>
                        <div className="grid gap-3 sm:grid-cols-2">
                            <select
                                aria-label="Filter by status"
                                value={filters.status ?? ''}
                                onChange={(event) =>
                                    updateFilters({
                                        status: event.target.value || null,
                                    })
                                }
                                className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px] dark:bg-input/30"
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
                                onChange={(event) =>
                                    updateFilters({
                                        priority: event.target.value || null,
                                    })
                                }
                                className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px] dark:bg-input/30"
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
                                                    <h2 className="font-medium">
                                                        {task.title}
                                                    </h2>
                                                    <Badge
                                                        variant={badgeVariant(
                                                            task,
                                                        )}
                                                    >
                                                        {label(task.status)}
                                                    </Badge>
                                                    <Badge variant="outline">
                                                        {label(task.priority)}
                                                    </Badge>
                                                    {task.is_due_soon &&
                                                        !task.is_overdue && (
                                                            <Badge variant="secondary">
                                                                Due soon
                                                            </Badge>
                                                        )}
                                                </div>
                                                {task.description && (
                                                    <p className="text-sm text-muted-foreground">
                                                        {task.description}
                                                    </p>
                                                )}
                                                {task.due_at && (
                                                    <p
                                                        className={
                                                            task.is_overdue
                                                                ? 'text-sm text-destructive'
                                                                : 'text-sm text-muted-foreground'
                                                        }
                                                    >
                                                        Due{' '}
                                                        {new Date(
                                                            task.due_at,
                                                        ).toLocaleString()}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="flex flex-wrap gap-2">
                                                {task.status === 'done' ? (
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={TaskController.reopen(
                                                                task.id,
                                                            )}
                                                            method="patch"
                                                            as="button"
                                                        >
                                                            <RotateCcw />
                                                            Reopen
                                                        </Link>
                                                    </Button>
                                                ) : (
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={TaskController.complete(
                                                                task.id,
                                                            )}
                                                            method="patch"
                                                            as="button"
                                                        >
                                                            <Check />
                                                            Complete
                                                        </Link>
                                                    </Button>
                                                )}
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        setEditor({
                                                            mode: 'edit',
                                                            task,
                                                        })
                                                    }
                                                >
                                                    <Pencil />
                                                    Edit
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="destructive"
                                                    asChild
                                                >
                                                    <Link
                                                        href={TaskController.destroy(
                                                            task.id,
                                                        )}
                                                        method="delete"
                                                        as="button"
                                                        onClick={(event) => {
                                                            if (
                                                                !window.confirm(
                                                                    `Delete “${task.title}”?`,
                                                                )
                                                            ) {
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
                    </>
                )}
            </div>

            <TaskCalendarDialog
                editor={editor}
                priorities={priorities}
                statuses={statuses}
                onOpenChange={(open) => {
                    if (!open) {
                        setEditor(null);
                    }
                }}
                onSuccess={() => {
                    setEditor(null);
                    setCalendarRefreshKey((currentKey) => currentKey + 1);
                }}
            />
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
