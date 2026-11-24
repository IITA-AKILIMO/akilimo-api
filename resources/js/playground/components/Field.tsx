import type {ReactNode} from 'react'

interface Props {
    id: string
    label: ReactNode
    error?: string
    hint?: ReactNode
    /** Grid column classes, e.g. "col-md-4" */
    col?: string
    /** Renders the control; `cls` is the Bootstrap control class including the invalid state. */
    children: (cls: string) => ReactNode
    /** select controls use form-select */
    select?: boolean
}

/** A Bootstrap form group: label, control, validation message and hint. */
export default function Field({id, label, error, hint, col = 'col-12', select = false, children}: Readonly<Props>) {
    const cls = `${select ? 'form-select' : 'form-control'}${error ? ' is-invalid' : ''}`
    return (
        <div className={col}>
            <label htmlFor={id} className="form-label fw-semibold small">{label}</label>
            {children(cls)}
            {error && <div className="invalid-feedback d-block">{error}</div>}
            {hint && <div className="form-text">{hint}</div>}
        </div>
    )
}
