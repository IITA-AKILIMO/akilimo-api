import {needsFertilizers, totalSteps} from './lib/scenario'
import type {Step} from './types'

interface Props {
    step: Step
    scenario: string
}

/** Progress through the wizard, as Bootstrap pills with a numbered (or ticked) badge. */
export default function Stepper({step, scenario}: Readonly<Props>) {
    const maxStep = totalSteps(scenario)

    const STEP_LABELS: Record<number, string> = {
        1: 'Scenario',
        2: 'Farm Details',
        3: 'Crop & Yield',
        4: needsFertilizers(scenario) ? 'Fertilizers' : 'Dates',
        5: 'Dates',
    }

    return (
        <ol className="nav nav-pills nav-fill gap-2 mb-3 list-unstyled" aria-label="Progress">
            {Array.from({length: maxStep}, (_, i) => i + 1).map((n) => {
                const active = step === n
                const done = step > n
                return (
                    <li key={n} className="nav-item" aria-current={active ? 'step' : undefined}>
                        <span className={`nav-link d-flex align-items-center justify-content-center gap-2 px-2 ${active ? 'active' : done ? 'text-success' : 'text-body-secondary'}`}>
                            <span className={`badge rounded-pill ${active ? 'text-bg-light' : done ? 'text-bg-success' : 'text-bg-secondary'}`}>
                                {done ? '✓' : n}
                            </span>
                            <span className="d-none d-md-inline">{STEP_LABELS[n]}</span>
                            {active && <span className="d-md-none">{STEP_LABELS[n]}</span>}
                        </span>
                    </li>
                )
            })}
        </ol>
    )
}
