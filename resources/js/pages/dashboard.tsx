import { Head, Link } from '@inertiajs/react';
import { CheckCircle2, CircleAlert, Clock3, ListTodo } from 'lucide-react';
import PageContent from '@/components/page-content';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as tasks } from '@/routes/tasks';
import type { TaskSummary } from '@/types';

const summaryCards = [
    {
        key: 'open',
        label: 'Open tasks',
        icon: ListTodo,
        filter: { status: null },
    },
    {
        key: 'overdue',
        label: 'Overdue',
        icon: CircleAlert,
        filter: { status: 'todo' },
    },
    {
        key: 'due_soon',
        label: 'Due soon',
        icon: Clock3,
        filter: { status: 'todo' },
    },
    {
        key: 'completed',
        label: 'Completed',
        icon: CheckCircle2,
        filter: { status: 'done' },
    },
] as const;

export default function Dashboard({
    taskSummary,
    todayRoutines,
}: {
    taskSummary: TaskSummary;
    todayRoutines: Array<{
        id: number;
        title: string;
        status: string;
        scheduled_for: string;
    }>;
}) {
    return (
        <>
            <Head title="Dashboard" />
            <PageContent className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Dashboard
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        A quick view of your current tasks.
                    </p>
                </div>
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {summaryCards.map(({ key, label, icon: Icon, filter }) => (
                        <Link
                            key={key}
                            href={tasks({ query: filter })}
                            className="rounded-xl focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        >
                            <Card className="h-full transition-colors hover:bg-accent/50">
                                <CardHeader className="flex-row items-center justify-between space-y-0">
                                    <CardTitle className="text-sm font-medium">
                                        {label}
                                    </CardTitle>
                                    <Icon className="size-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <p className="text-3xl font-semibold">
                                        {taskSummary[key]}
                                    </p>
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>Routines due today</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3">
                        {todayRoutines.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No routines are due today.
                            </p>
                        ) : (
                            todayRoutines.map((routine) => (
                                <div
                                    key={routine.id}
                                    className="flex items-center justify-between gap-4 rounded-lg border p-3"
                                >
                                    <div>
                                        <p className="font-medium">
                                            {routine.title}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {new Date(
                                                routine.scheduled_for,
                                            ).toLocaleTimeString()}
                                        </p>
                                    </div>
                                    <span className="text-sm capitalize">
                                        {routine.status}
                                    </span>
                                </div>
                            ))
                        )}
                    </CardContent>
                </Card>
            </PageContent>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
