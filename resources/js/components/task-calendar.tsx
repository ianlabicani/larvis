import FullCalendar from '@fullcalendar/react';
import type {
    DateClickInfo,
    DatesSetInfo,
    EventClickInfo,
} from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/react/daygrid';
import interactionPlugin from '@fullcalendar/react/interaction';
import themePlugin from '@fullcalendar/react/themes/breezy';
import timeGridPlugin from '@fullcalendar/react/timegrid';
import '@fullcalendar/react/skeleton.css';
import '@fullcalendar/react/themes/breezy/theme.css';
import '@fullcalendar/react/themes/breezy/palettes/emerald.css';
import { useHttp } from '@inertiajs/react';
import { AlertCircle } from 'lucide-react';
import { useCallback, useState } from 'react';
import 'temporal-polyfill/global';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import { events as calendarEvents } from '@/routes/calendar';
import type { CalendarEvent } from '@/types';

type CalendarRange = {
    start: string;
    end: string;
};

function label(value: string): string {
    return value
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

export default function TaskCalendar({
    timezone,
    onCreate,
    onEdit,
}: {
    timezone: string;
    onCreate: (dueAt: string) => void;
    onEdit: (event: CalendarEvent) => void;
}) {
    const { submit, processing } = useHttp();
    const [events, setEvents] = useState<CalendarEvent[]>([]);
    const [hasLoaded, setHasLoaded] = useState(false);
    const [range, setRange] = useState<CalendarRange | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [routinePreview, setRoutinePreview] = useState<CalendarEvent | null>(
        null,
    );

    const loadEvents = useCallback(
        async (nextRange: CalendarRange): Promise<void> => {
            setRange(nextRange);
            setError(null);

            try {
                const result = (await submit(
                    calendarEvents({
                        query: nextRange,
                    }),
                )) as CalendarEvent[];

                setEvents(result);
            } catch {
                setError('The calendar could not be loaded. Please try again.');
            } finally {
                setHasLoaded(true);
            }
        },
        [submit],
    );

    function handleDatesSet(info: DatesSetInfo): void {
        void loadEvents({ start: info.startStr, end: info.endStr });
    }

    function handleEventClick(info: EventClickInfo): void {
        info.jsEvent.preventDefault();

        const event = events.find(
            (candidate) => candidate.id === info.event.id,
        );

        if (event) {
            if (event.type === 'routine') {
                setRoutinePreview(event);
            } else {
                onEdit(event);
            }
        }
    }

    function handleDateClick(info: DateClickInfo): void {
        onCreate(`${info.dateStr.slice(0, 10)}T09:00:00`);
    }

    return (
        <>
            {error && (
                <Alert variant="destructive">
                    <AlertCircle />
                    <AlertTitle>Calendar unavailable</AlertTitle>
                    <AlertDescription>
                        <p>{error}</p>
                        {range && (
                            <Button
                                className="mt-2"
                                size="sm"
                                variant="outline"
                                onClick={() => void loadEvents(range)}
                            >
                                Try again
                            </Button>
                        )}
                    </AlertDescription>
                </Alert>
            )}

            <div className="relative h-[calc(100dvh-15rem)] min-h-0 rounded-lg border bg-card p-3 shadow-sm sm:p-6">
                {!hasLoaded && processing && (
                    <div className="absolute inset-0 z-10 flex items-center justify-center gap-2 rounded-lg bg-background/80 text-sm text-muted-foreground">
                        <Spinner />
                        Loading calendar…
                    </div>
                )}

                <FullCalendar
                    plugins={[
                        themePlugin,
                        dayGridPlugin,
                        timeGridPlugin,
                        interactionPlugin,
                    ]}
                    initialView="dayGridMonth"
                    timeZone={timezone}
                    headerToolbar={{
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek',
                    }}
                    events={events}
                    eventInteractive
                    eventContent={({ event }) => {
                        const { priority, status, type } =
                            event.extendedProps as Pick<
                                CalendarEvent,
                                'priority' | 'status' | 'type'
                            >;

                        return (
                            <span
                                className="flex min-w-0 items-center gap-1 text-xs"
                                aria-label={`${type === 'routine' ? 'Routine' : `${label(priority ?? 'medium')} priority`}, ${label(status)}, ${event.title}`}
                            >
                                <span className="shrink-0 font-medium">
                                    {type === 'routine'
                                        ? 'Routine'
                                        : label(priority ?? 'medium')}
                                </span>
                                <span aria-hidden="true">·</span>
                                <span className="shrink-0">
                                    {label(status)}
                                </span>
                                <span aria-hidden="true">·</span>
                                <span className="truncate">{event.title}</span>
                            </span>
                        );
                    }}
                    datesSet={handleDatesSet}
                    dateClick={handleDateClick}
                    eventClick={handleEventClick}
                    noEventsContent={() =>
                        hasLoaded
                            ? 'No tasks or routines are due in this range.'
                            : 'Loading calendar…'
                    }
                    height="100%"
                    expandRows
                />
            </div>
            <Dialog
                open={routinePreview !== null}
                onOpenChange={(open) => !open && setRoutinePreview(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{routinePreview?.title}</DialogTitle>
                        <DialogDescription>
                            Read-only routine occurrence
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-2 text-sm">
                        {routinePreview?.description && (
                            <p>{routinePreview.description}</p>
                        )}
                        <p>
                            <span className="font-medium">Scheduled:</span>{' '}
                            {routinePreview &&
                                new Date(routinePreview.start).toLocaleString()}
                        </p>
                        <p>
                            <span className="font-medium">Status:</span>{' '}
                            {routinePreview && label(routinePreview.status)}
                        </p>
                        <p>
                            <span className="font-medium">Timezone:</span>{' '}
                            {routinePreview?.timezone}
                        </p>
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
