import { Head, Link } from '@inertiajs/react';
import { CheckCircle2, CircleAlert, Clock3, ListTodo } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as tasks } from '@/routes/tasks';
import type { TaskSummary } from '@/types';

const summaryCards = [
    { key: 'open', label: 'Open tasks', icon: ListTodo, filter: { status: null } },
    { key: 'overdue', label: 'Overdue', icon: CircleAlert, filter: { status: 'todo' } },
    { key: 'due_soon', label: 'Due soon', icon: Clock3, filter: { status: 'todo' } },
    { key: 'completed', label: 'Completed', icon: CheckCircle2, filter: { status: 'done' } },
] as const;

export default function Dashboard({ taskSummary }: { taskSummary: TaskSummary }) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Dashboard</h1>
                    <p className="mt-1 text-sm text-muted-foreground">A quick view of your current tasks.</p>
                </div>
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {summaryCards.map(({ key, label, icon: Icon, filter }) => (
                        <Link
                            key={key}
                            href={tasks({ query: filter })}
                            className="rounded-xl focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:outline-none"
                        >
                            <Card className="h-full transition-colors hover:bg-accent/50">
                                <CardHeader className="flex-row items-center justify-between space-y-0">
                                    <CardTitle className="text-sm font-medium">{label}</CardTitle>
                                    <Icon className="size-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <p className="text-3xl font-semibold">{taskSummary[key]}</p>
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </div>
            </div>
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
