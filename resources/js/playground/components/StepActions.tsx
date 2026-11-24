import type {ReactNode} from 'react'
import Icon from './Icon'
import Spinner from './Spinner'

interface Props {
    onBack?: () => void
    /** Last step: shows the submit button instead of Continue */
    isLastStep?: boolean
    submitting?: boolean
    onNext?: () => void
    onSubmit?: () => void
    children?: ReactNode
}

/** The Back / Continue / Get Recommendations row at the foot of every wizard step. */
export default function StepActions({onBack, isLastStep = false, submitting = false, onNext, onSubmit}: Readonly<Props>) {
    return (
        <div className="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
            {onBack
                ? <button type="button" className="btn btn-outline-secondary" onClick={onBack}>← Back</button>
                : <span/>}
            {isLastStep
                ? (
                    <button type="button" className="btn btn-primary d-inline-flex align-items-center gap-2"
                            onClick={onSubmit} disabled={submitting}>
                        {submitting ? <Spinner text="Computing…"/> : <><Icon name="play"/> Get Recommendations</>}
                    </button>
                )
                : (
                    <button type="button" className="btn btn-primary d-inline-flex align-items-center gap-2" onClick={onNext}>
                        Continue <Icon name="next"/>
                    </button>
                )}
        </div>
    )
}
