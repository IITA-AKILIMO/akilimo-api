import type { ReactNode } from 'react'

interface FormSectionProps {
    title: string
    /** One line saying what the group is for. */
    hint?: string
    children: ReactNode
}

/**
 * A titled group of fields inside a long form, so a screen with a dozen fields reads as a few small steps
 * instead of one dense grid. Put the usual `row g-3` inside it.
 */
export default function FormSection({ title, hint, children }: FormSectionProps) {
    return (
        <section className="mb-4">
            <div className="border-bottom pb-2 mb-3">
                <h2 className="h6 fw-semibold mb-0">{title}</h2>
                {hint && <div className="small text-body-secondary">{hint}</div>}
            </div>
            {children}
        </section>
    )
}
