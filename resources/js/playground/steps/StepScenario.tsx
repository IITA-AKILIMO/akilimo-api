import StepActions from '../components/StepActions'
import Icon from '../components/Icon'
import {SCENARIOS} from '../constants'
import type {Scenario} from '../types'

interface Props {
    scenario: Scenario
    onSelect: (s: Scenario) => void
    onNext: () => void
}

export default function StepScenario({scenario, onSelect, onNext}: Readonly<Props>) {
    return (
        <div className="card">
            <div className="card-body p-4">
                <h2 className="h5 fw-bold">What would you like to know?</h2>
                <p className="text-body-secondary">Choose a recommendation type for your farm.</p>

                <div className="row g-3" role="radiogroup" aria-label="Recommendation type">
                    {SCENARIOS.map((s) => {
                        const active = scenario === s.id
                        return (
                            <div key={s.id} className="col-12 col-sm-6 col-lg-4">
                                <button type="button" role="radio" aria-checked={active}
                                        className={`card h-100 w-100 text-start ${active ? 'border-primary bg-primary-subtle' : 'bg-body'}`}
                                        onClick={() => onSelect(s.id)}>
                                    <span className="card-body">
                                        <span className="fs-3 d-block mb-1" aria-hidden="true">{s.icon}</span>
                                        <span className="fw-semibold d-block">{s.title}</span>
                                        <span className="small text-body-secondary d-block">{s.desc}</span>
                                    </span>
                                </button>
                            </div>
                        )
                    })}
                </div>

                {scenario === 'IC' && (
                    <div className="alert alert-info d-flex align-items-center gap-2 mt-3 mb-0 py-2 small" role="note">
                        <Icon name="info" size={15}/>
                        Intercropping: 🌾 maize available for Nigeria only · 🥔 potato for Tanzania only.
                    </div>
                )}

                <StepActions onNext={onNext}/>
            </div>
        </div>
    )
}
