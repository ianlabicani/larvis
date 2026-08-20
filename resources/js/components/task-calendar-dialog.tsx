import { Form } from '@inertiajs/react';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { CalendarEvent, Task } from '@/types';

type TaskEditor =
    | {
          mode: 'create';
          dueAt?: string;
      }
    | {
          mode: 'edit';
          task: CalendarEvent | Task;
      };

function label(value: string): string {
    return value
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function dueAt(task: CalendarEvent | Task): string | null {
    return 'start' in task ? task.start : task.due_at;
}

export type { TaskEditor };

export default function TaskCalendarDialog({
    editor,
    priorities,
    statuses,
    onOpenChange,
    onSuccess,
}: {
    editor: TaskEditor | null;
    priorities: string[];
    statuses: string[];
    onOpenChange: (open: boolean) => void;
    onSuccess: () => void;
}) {
    const editingTask = editor?.mode === 'edit' ? editor.task : null;
    const isEditing = editor?.mode === 'edit';
    const form = editingTask
        ? TaskController.update.form(Number(editingTask.id))
        : TaskController.store.form();

    return (
        <Dialog open={editor !== null} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[calc(100dvh-2rem)] overflow-y-auto sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing ? 'Edit task' : 'New task'}
                    </DialogTitle>
                    <DialogDescription>
                        {isEditing
                            ? 'Update the task details and schedule.'
                            : 'Add a task to your calendar.'}
                    </DialogDescription>
                </DialogHeader>

                <Form {...form} onSuccess={onSuccess} className="space-y-5">
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="task-title">Title</Label>
                                <Input
                                    id="task-title"
                                    name="title"
                                    defaultValue={editingTask?.title}
                                    required
                                    autoFocus
                                    maxLength={255}
                                    placeholder="What needs to be done?"
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="task-description">
                                    Description
                                </Label>
                                <textarea
                                    id="task-description"
                                    name="description"
                                    defaultValue={
                                        editingTask?.description ?? ''
                                    }
                                    rows={5}
                                    maxLength={5000}
                                    placeholder="Optional details"
                                    className="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid gap-5 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="task-priority">
                                        Priority
                                    </Label>
                                    <select
                                        id="task-priority"
                                        name="priority"
                                        defaultValue={
                                            editingTask?.priority ?? 'medium'
                                        }
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px] dark:bg-input/30"
                                    >
                                        {priorities.map((priority) => (
                                            <option
                                                key={priority}
                                                value={priority}
                                            >
                                                {label(priority)}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.priority} />
                                </div>

                                {editingTask && (
                                    <div className="grid gap-2">
                                        <Label htmlFor="task-status">
                                            Status
                                        </Label>
                                        <select
                                            id="task-status"
                                            name="status"
                                            defaultValue={editingTask.status}
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px] dark:bg-input/30"
                                        >
                                            {statuses.map((status) => (
                                                <option
                                                    key={status}
                                                    value={status}
                                                >
                                                    {label(status)}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.status} />
                                    </div>
                                )}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="task-due-at">Due date</Label>
                                <Input
                                    id="task-due-at"
                                    name="due_at"
                                    type="datetime-local"
                                    defaultValue={
                                        editor?.mode === 'create'
                                            ? editor.dueAt?.slice(0, 16)
                                            : ((editingTask &&
                                                  dueAt(editingTask)?.slice(
                                                      0,
                                                      16,
                                                  )) ??
                                              undefined)
                                    }
                                />
                                <InputError message={errors.due_at} />
                            </div>

                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => onOpenChange(false)}
                                >
                                    Cancel
                                </Button>
                                <Button disabled={processing}>
                                    {isEditing ? 'Save changes' : 'Create task'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
