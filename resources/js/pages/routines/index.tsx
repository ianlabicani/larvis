import { Form, Head, Link } from '@inertiajs/react';
import { Check, CirclePause, CirclePlay, Pencil, Plus } from 'lucide-react';
import { useState } from 'react';
import RoutineController from '@/actions/App/Http/Controllers/RoutineController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PageContent from '@/components/page-content';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { index } from '@/routes/routines';
import type { Routine, TodayRoutineOccurrence } from '@/types';

type Editor = { mode: 'create' } | { mode: 'edit'; routine: Routine };

function titleCase(value: string): string {
    return value
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function RoutineEditor({
    editor,
    frequencies,
    timezones,
    onClose,
}: {
    editor: Editor | null;
    frequencies: string[];
    timezones: string[];
    onClose: () => void;
}) {
    const routine = editor?.mode === 'edit' ? editor.routine : null;
    const route = routine
        ? RoutineController.update.form(routine.id)
        : RoutineController.store.form();

    return (
        <Dialog
            open={editor !== null}
            onOpenChange={(open) => !open && onClose()}
        >
            <DialogContent className="max-h-[90dvh] overflow-y-auto sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>
                        {routine ? 'Edit routine' : 'New routine'}
                    </DialogTitle>
                    <DialogDescription>
                        Set a repeatable activity in its local timezone.
                    </DialogDescription>
                </DialogHeader>
                <Form {...route} onSuccess={onClose} className="grid gap-4">
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="routine-title">Title</Label>
                                <Input
                                    id="routine-title"
                                    name="title"
                                    defaultValue={routine?.title}
                                    required
                                    autoFocus
                                />
                                <InputError message={errors.title} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="routine-description">
                                    Description
                                </Label>
                                <textarea
                                    id="routine-description"
                                    name="description"
                                    defaultValue={routine?.description ?? ''}
                                    rows={3}
                                    className="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                />
                                <InputError message={errors.description} />
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="routine-frequency">
                                        Frequency
                                    </Label>
                                    <select
                                        id="routine-frequency"
                                        name="frequency"
                                        defaultValue={
                                            routine?.frequency ?? 'daily'
                                        }
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs"
                                    >
                                        {frequencies.map((frequency) => (
                                            <option
                                                key={frequency}
                                                value={frequency}
                                            >
                                                {titleCase(frequency)}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="routine-time">
                                        Local time
                                    </Label>
                                    <Input
                                        id="routine-time"
                                        name="scheduled_time"
                                        type="time"
                                        defaultValue={
                                            routine?.scheduled_time.slice(
                                                0,
                                                5,
                                            ) ?? '06:00'
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.scheduled_time}
                                    />
                                </div>
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="routine-timezone">
                                    Timezone
                                </Label>
                                <select
                                    id="routine-timezone"
                                    name="timezone"
                                    defaultValue={
                                        routine?.timezone ?? 'Asia/Manila'
                                    }
                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs"
                                >
                                    {timezones.map((timezone) => (
                                        <option key={timezone} value={timezone}>
                                            {timezone}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.timezone} />
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="routine-start">
                                        Start date
                                    </Label>
                                    <Input
                                        id="routine-start"
                                        name="starts_on"
                                        type="date"
                                        defaultValue={
                                            routine?.starts_on ??
                                            new Date()
                                                .toISOString()
                                                .slice(0, 10)
                                        }
                                        required
                                    />
                                    <InputError message={errors.starts_on} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="routine-end">
                                        End date
                                    </Label>
                                    <Input
                                        id="routine-end"
                                        name="ends_on"
                                        type="date"
                                        defaultValue={routine?.ends_on ?? ''}
                                    />
                                    <InputError message={errors.ends_on} />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={onClose}
                                >
                                    Cancel
                                </Button>
                                <Button disabled={processing}>
                                    {routine
                                        ? 'Save changes'
                                        : 'Create routine'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

export default function RoutinesIndex({
    routines,
    todayOccurrences,
    frequencies,
    timezones,
}: {
    routines: Routine[];
    todayOccurrences: TodayRoutineOccurrence[];
    frequencies: string[];
    timezones: string[];
}) {
    const [editor, setEditor] = useState<Editor | null>(null);

    return (
        <>
            <Head title="Routines" />
            <PageContent className="space-y-6">
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <Heading
                        variant="small"
                        title="Routines"
                        description="Build consistency one day at a time."
                    />
                    <Button onClick={() => setEditor({ mode: 'create' })}>
                        <Plus />
                        New routine
                    </Button>
                </div>

                <section className="space-y-3" aria-labelledby="today-routines">
                    <h2 id="today-routines" className="text-lg font-semibold">
                        Today
                    </h2>
                    {todayOccurrences.length === 0 ? (
                        <Card>
                            <CardContent className="py-8 text-center text-sm text-muted-foreground">
                                No routines are due today.
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-3 sm:grid-cols-2">
                            {todayOccurrences.map((occurrence) => (
                                <Card key={occurrence.id}>
                                    <CardContent className="flex items-center justify-between gap-4 pt-6">
                                        <div className="min-w-0">
                                            <p className="truncate font-medium">
                                                {occurrence.routine.title}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {occurrence.routine.scheduled_time.slice(
                                                    0,
                                                    5,
                                                )}{' '}
                                                · {occurrence.routine.timezone}
                                            </p>
                                        </div>
                                        {occurrence.status === 'completed' ? (
                                            <Badge variant="secondary">
                                                Completed
                                            </Badge>
                                        ) : (
                                            <Button size="sm" asChild>
                                                <Link
                                                    href={RoutineController.complete(
                                                        occurrence.id,
                                                    )}
                                                    method="patch"
                                                    as="button"
                                                >
                                                    <Check />
                                                    Complete
                                                </Link>
                                            </Button>
                                        )}
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </section>

                <section className="space-y-3" aria-labelledby="all-routines">
                    <h2 id="all-routines" className="text-lg font-semibold">
                        All routines
                    </h2>
                    <div className="grid gap-4 lg:grid-cols-2">
                        {routines.map((routine) => (
                            <Card key={routine.id}>
                                <CardHeader className="flex-row items-start justify-between gap-3">
                                    <div>
                                        <CardTitle>{routine.title}</CardTitle>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            Daily at{' '}
                                            {routine.scheduled_time.slice(0, 5)}{' '}
                                            · {routine.timezone}
                                        </p>
                                    </div>
                                    <Badge
                                        variant={
                                            routine.status === 'active'
                                                ? 'default'
                                                : 'secondary'
                                        }
                                    >
                                        {titleCase(routine.status)}
                                    </Badge>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {routine.description && (
                                        <p className="text-sm text-muted-foreground">
                                            {routine.description}
                                        </p>
                                    )}
                                    <p className="text-sm">
                                        Next:{' '}
                                        {routine.next_occurrence_at
                                            ? new Date(
                                                  routine.next_occurrence_at,
                                              ).toLocaleString()
                                            : 'Not scheduled'}
                                    </p>
                                    <div className="flex flex-wrap gap-2">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            onClick={() =>
                                                setEditor({
                                                    mode: 'edit',
                                                    routine,
                                                })
                                            }
                                        >
                                            <Pencil />
                                            Edit
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            asChild
                                        >
                                            <Link
                                                href={
                                                    routine.status === 'active'
                                                        ? RoutineController.pause(
                                                              routine.id,
                                                          )
                                                        : RoutineController.resume(
                                                              routine.id,
                                                          )
                                                }
                                                method="patch"
                                                as="button"
                                            >
                                                {routine.status === 'active' ? (
                                                    <CirclePause />
                                                ) : (
                                                    <CirclePlay />
                                                )}
                                                {routine.status === 'active'
                                                    ? 'Pause'
                                                    : 'Resume'}
                                            </Link>
                                        </Button>
                                    </div>
                                    <div className="border-t pt-3">
                                        <p className="mb-2 text-sm font-medium">
                                            Recent history
                                        </p>
                                        <div className="flex flex-wrap gap-2">
                                            {routine.occurrences.length ===
                                            0 ? (
                                                <span className="text-sm text-muted-foreground">
                                                    No history yet.
                                                </span>
                                            ) : (
                                                routine.occurrences
                                                    .slice(0, 7)
                                                    .map((occurrence) => (
                                                        <Badge
                                                            key={occurrence.id}
                                                            variant="outline"
                                                        >
                                                            {
                                                                occurrence.local_date
                                                            }
                                                            :{' '}
                                                            {titleCase(
                                                                occurrence.status,
                                                            )}
                                                        </Badge>
                                                    ))
                                            )}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </section>
            </PageContent>
            <RoutineEditor
                editor={editor}
                frequencies={frequencies}
                timezones={timezones}
                onClose={() => setEditor(null)}
            />
        </>
    );
}

RoutinesIndex.layout = { breadcrumbs: [{ title: 'Routines', href: index() }] };
