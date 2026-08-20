import { Form, Link } from '@inertiajs/react';
import type { ComponentProps } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/tasks';
import type { Task } from '@/types';

type TaskFormProps = {
    form: Pick<ComponentProps<typeof Form>, 'action' | 'method'>;
    priorities: string[];
    statuses: string[];
    submitLabel: string;
    task?: Task;
};

function label(value: string): string {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

export default function TaskForm({
    form,
    priorities,
    statuses,
    submitLabel,
    task,
}: TaskFormProps) {
    return (
        <div className="space-y-6">
            <Heading
                variant="small"
                title={task ? 'Edit task' : 'Create task'}
                description={
                    task
                        ? 'Update the task details and progress.'
                        : 'Add a task to keep track of what matters next.'
                }
            />

            <Form {...form} className="space-y-6">
                {({ errors, processing }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="title">Title</Label>
                            <Input
                                id="title"
                                name="title"
                                defaultValue={task?.title}
                                required
                                autoFocus
                                maxLength={255}
                                placeholder="What needs to be done?"
                            />
                            <InputError message={errors.title} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="description">Description</Label>
                            <textarea
                                id="description"
                                name="description"
                                defaultValue={task?.description ?? ''}
                                rows={5}
                                maxLength={5000}
                                placeholder="Optional details"
                                className="border-input bg-transparent focus-visible:border-ring focus-visible:ring-ring/50 dark:bg-input/30 min-h-24 rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                            />
                            <InputError message={errors.description} />
                        </div>

                        <div className="grid gap-6 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="priority">Priority</Label>
                                <select
                                    id="priority"
                                    name="priority"
                                    defaultValue={task?.priority ?? 'medium'}
                                    className="border-input bg-background dark:bg-input/30 h-9 rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                >
                                    {priorities.map((priority) => (
                                        <option key={priority} value={priority}>
                                            {label(priority)}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.priority} />
                            </div>

                            {task && (
                                <div className="grid gap-2">
                                    <Label htmlFor="status">Status</Label>
                                    <select
                                        id="status"
                                        name="status"
                                        defaultValue={task.status}
                                        className="border-input bg-background dark:bg-input/30 h-9 rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                    >
                                        {statuses.map((status) => (
                                            <option key={status} value={status}>
                                                {label(status)}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.status} />
                                </div>
                            )}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="due_at">Due date</Label>
                            <Input
                                id="due_at"
                                name="due_at"
                                type="datetime-local"
                                defaultValue={task?.due_at?.slice(0, 16)}
                            />
                            <InputError message={errors.due_at} />
                        </div>

                        <div className="flex items-center gap-3">
                            <Button disabled={processing}>{submitLabel}</Button>
                            <Button variant="outline" asChild>
                                <Link href={index()}>Cancel</Link>
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </div>
    );
}
