import { Head } from '@inertiajs/react';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import TaskForm from '@/components/task-form';
import { create, index } from '@/routes/tasks';

export default function CreateTask({
    priorities,
    statuses,
}: {
    priorities: string[];
    statuses: string[];
}) {
    return (
        <>
            <Head title="Create task" />
            <TaskForm
                form={TaskController.store.form()}
                priorities={priorities}
                statuses={statuses}
                submitLabel="Create task"
            />
        </>
    );
}

CreateTask.layout = {
    breadcrumbs: [
        {
            title: 'Tasks',
            href: index(),
        },
        {
            title: 'Create task',
            href: create(),
        },
    ],
};
