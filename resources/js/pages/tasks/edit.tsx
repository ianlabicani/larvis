import { Head } from '@inertiajs/react';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import TaskForm from '@/components/task-form';
import { edit, index } from '@/routes/tasks';
import type { Task } from '@/types';

export default function EditTask({
    task,
    priorities,
    statuses,
}: {
    task: Task;
    priorities: string[];
    statuses: string[];
}) {
    return (
        <>
            <Head title={`Edit ${task.title}`} />
            <TaskForm
                form={TaskController.update.form(task.id)}
                priorities={priorities}
                statuses={statuses}
                submitLabel="Save changes"
                task={task}
            />
        </>
    );
}

EditTask.layout = {
    breadcrumbs: [
        {
            title: 'Tasks',
            href: index(),
        },
        {
            title: 'Edit task',
            href: edit(1),
        },
    ],
};
