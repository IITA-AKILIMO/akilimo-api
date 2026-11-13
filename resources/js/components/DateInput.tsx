import { useId, useRef } from 'react'

/** Today as yyyy-mm-dd in the browser's own time zone (toISOString would give the UTC date). */
export function todayISO(): string {
    const d = new Date()
    const pad = (n: number) => String(n).padStart(2, '0')

    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

interface DateInputProps {
    /** yyyy-mm-dd, or an empty string for no date: the same shape the server sends and expects. */
    value: string
    onChange: (value: string) => void
    /** Earliest and latest date that can be picked (yyyy-mm-dd). */
    min?: string
    max?: string
    error?: string
    /** A compact control for filter rows and tables: just the field and a clear button. */
    small?: boolean
    required?: boolean
    disabled?: boolean
    id?: string
    /** What the date is for when there is no visible label (a filter, for example). */
    ariaLabel?: string
    /** A "Today" shortcut under the field. On by default in forms, never in the compact size. */
    today?: boolean
}

/**
 * One date field for every form and filter: the browser's own calendar picker (so the date is always valid
 * and in the user's language), opened by clicking anywhere in the field. A full-size field has small "Today"
 * and "Clear" links under it, so it keeps the full width of its column; the compact size has a clear button
 * beside it. The value stays a yyyy-mm-dd string, so nothing changes on the server.
 */
export default function DateInput({ value, onChange, min, max, error, small, required, disabled, id, ariaLabel, today }: DateInputProps) {
    const input = useRef<HTMLInputElement>(null)
    const generatedId = useId()
    const showToday = (today ?? !small) && !small
    const now = todayISO()
    const todayAllowed = (!min || now >= min) && (!max || now <= max)
    const canClear = value !== '' && !required && !disabled

    function openPicker() {
        try {
            input.current?.showPicker()
        } catch {
            // an older browser, or the picker is already open: the field still accepts typing
        }
    }

    const field = (
        <input
            ref={input}
            id={id ?? generatedId}
            type="date"
            className={`form-control${small ? ' form-control-sm' : ''}${error ? ' is-invalid' : ''}`}
            value={value}
            min={min}
            max={max}
            required={required}
            disabled={disabled}
            aria-label={ariaLabel}
            aria-invalid={error ? true : undefined}
            onChange={(e) => onChange(e.target.value)}
            onClick={openPicker}
        />
    )

    if (small) {
        return (
            <div className="input-group input-group-sm flex-nowrap">
                {field}
                {canClear && (
                    <button type="button" className="btn btn-outline-secondary" onClick={() => onChange('')} aria-label="Clear the date" title="Clear the date">
                        <span aria-hidden="true">×</span>
                    </button>
                )}
            </div>
        )
    }

    return (
        <div>
            {field}
            {(showToday || canClear) && (
                <div className="d-flex gap-3 mt-1 small">
                    {showToday && (
                        <button
                            type="button"
                            className="btn btn-link btn-sm p-0"
                            onClick={() => onChange(now)}
                            disabled={disabled || !todayAllowed}
                            title={todayAllowed ? 'Set to today' : 'Today is outside the allowed dates'}
                        >
                            Today
                        </button>
                    )}
                    {canClear && (
                        <button type="button" className="btn btn-link btn-sm p-0 text-body-secondary" onClick={() => onChange('')}>
                            Clear
                        </button>
                    )}
                </div>
            )}
        </div>
    )
}
