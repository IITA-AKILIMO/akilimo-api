import SubmitButton from './SubmitButton'
import type {FertilizerEntry} from './types'

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
        <div className="pg-card">
            <h2 className="pg-card-title">Fertilizer selection</h2>
            <p className="pg-card-sub">
                Tick the fertilizers you can buy. Prices are prefilled with the typical price in the AKILIMO price
                list; change them to what you pay.
            </p>

            {loading && (
                <div className="fert-loading">
                    <div className="fert-spinner"/>
                    Loading fertilizers for {country}…
                </div>
            )}

            {!loading && fertilizers.length === 0 && (
                <p style={{color: 'var(--text-muted)', fontSize: '0.875rem'}}>
                    No fertilizers found for this country. The recommendation will use model defaults.
                </p>
            )}

            {!loading && fertilizers.length > 0 && (
                <div style={{overflowX: 'auto'}}>
                    <table className="fert-table">
                        <thead>
                        <tr>
                            <th style={{width: 36}}>Use</th>
                            <th>Fertilizer</th>
                            <th style={{width: 96}}>Bag (kg)</th>
                            <th>{priceLabel}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {fertilizers.map((f) => (
                            <tr key={f.key}>
                                <td>
                                    <input id={`fert-${f.key}`} type="checkbox" checked={f.selected}
                                           onChange={(e) => onUpdate(f.key, {selected: e.target.checked})}/>
                                </td>
                                <td style={{fontWeight: f.selected ? 600 : 400}}>
                                    <label className="fert-name" htmlFor={`fert-${f.key}`}>{f.name}</label>
                                </td>
                                <td data-label="Bag (kg)">
                                    <input type="number" min="0" value={f.weight}
                                           aria-label={`${f.name} bag weight in kg`}
                                           onChange={(e) => onUpdate(f.key, {weight: +e.target.value})}/>
                                </td>
                                <td data-label={priceLabel}>
                                    <input type="number" min="0" step="any" inputMode="decimal" disabled={!f.selected}
                                           aria-label={`${f.name} ${priceLabel.toLowerCase()}`}
                                           placeholder={f.selected ? 'e.g. 30000' : '—'}
                                           value={f.price || ''}
                                           onChange={(e) => onUpdate(f.key, {price: +e.target.value})}
                                           style={{opacity: f.selected ? 1 : 0.5}}/>
                                    {errors[`price_${f.key}`] && (
                                        <div className="pg-field-error" style={{marginTop: '0.25rem'}}>
                                            {errors[`price_${f.key}`]}
                                        </div>
                                    )}
                                </td>
                            </tr>
                        ))}
                        </tbody>
                    </table>
                    {errors.fertilizers && (
                        <p className="pg-field-error" style={{marginTop: '0.5rem'}}>{errors.fertilizers}</p>
                    )}
                </div>
            )}

            <div className="pg-actions">
                <button className="btn btn-ghost" onClick={onBack}>← Back</button>
                {isLastStep
                    ? <SubmitButton submitting={submitting} onClick={onSubmit}/>
                    : <button className="btn btn-primary" onClick={onNext}>
                        Continue
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                }
            </div>
        </div>
    )
}
