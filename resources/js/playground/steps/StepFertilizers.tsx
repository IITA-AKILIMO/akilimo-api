import Spinner from '../components/Spinner'
import StepActions from '../components/StepActions'
import type {FertilizerEntry} from '../types'

interface Props {
    country: string
    currency: string | null
    fertilizers: FertilizerEntry[]
    loading: boolean
    errors: Record<string, string>
    isLastStep: boolean
    submitting: boolean
    onUpdate: (key: string, patch: Partial<FertilizerEntry>) => void
    onBack: () => void
    onNext: () => void
    onSubmit: () => void
}

export default function StepFertilizers({
    country, currency, fertilizers, loading, errors, isLastStep, submitting,
    onUpdate, onBack, onNext, onSubmit,
}: Readonly<Props>) {
    const priceLabel = `Price per bag${currency ? ` (${currency})` : ''}`

    return (
        <div className="card">
            <div className="card-body p-4">
                <h2 className="h5 fw-bold">Fertilizer selection</h2>
                <p className="text-body-secondary">
                    Tick the fertilizers you can buy. Prices are prefilled with the typical price in the AKILIMO price
                    list; change them to what you pay.
                </p>

                {loading && <div className="py-4 text-center text-body-secondary"><Spinner text={`Loading fertilizers for ${country}…`}/></div>}

                {!loading && fertilizers.length === 0 && (
                    <p className="text-body-secondary small">
                        No fertilizers found for this country. The recommendation will use model defaults.
                    </p>
                )}

                {!loading && fertilizers.length > 0 && (
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead>
                            <tr>
                                <th scope="col">Use</th>
                                <th scope="col">Fertilizer</th>
                                <th scope="col">Bag (kg)</th>
                                <th scope="col">{priceLabel}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {fertilizers.map((f) => {
                                const priceError = errors[`price_${f.key}`]
                                return (
                                    <tr key={f.key}>
                                        <td>
                                            <input id={`fert-${f.key}`} type="checkbox" className="form-check-input"
                                                   checked={f.selected}
                                                   onChange={(e) => onUpdate(f.key, {selected: e.target.checked})}/>
                                        </td>
                                        <td className={f.selected ? 'fw-semibold' : ''}>
                                            <label htmlFor={`fert-${f.key}`}>{f.name}</label>
                                        </td>
                                        <td>
                                            <input type="number" min="0" className="form-control form-control-sm"
                                                   value={f.weight} aria-label={`${f.name} bag weight in kg`}
                                                   onChange={(e) => onUpdate(f.key, {weight: +e.target.value})}/>
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="any" inputMode="decimal"
                                                   className={`form-control form-control-sm${priceError ? ' is-invalid' : ''}`}
                                                   disabled={!f.selected}
                                                   aria-label={`${f.name} ${priceLabel.toLowerCase()}`}
                                                   placeholder={f.selected ? 'e.g. 30000' : '—'}
                                                   value={f.price || ''}
                                                   onChange={(e) => onUpdate(f.key, {price: +e.target.value})}/>
                                            {priceError && <div className="invalid-feedback">{priceError}</div>}
                                        </td>
                                    </tr>
                                )
                            })}
                            </tbody>
                        </table>
                        {errors.fertilizers && <div className="invalid-feedback d-block mt-2">{errors.fertilizers}</div>}
                    </div>
                )}

                <StepActions onBack={onBack} isLastStep={isLastStep} submitting={submitting}
                             onNext={onNext} onSubmit={onSubmit}/>
            </div>
        </div>
    )
}
