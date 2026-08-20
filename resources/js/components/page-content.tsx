import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

export default function PageContent({
    className,
    ...props
}: ComponentProps<'div'>) {
    return (
        <div
            data-slot="page-content"
            className={cn('px-4 py-6', className)}
            {...props}
        />
    );
}
