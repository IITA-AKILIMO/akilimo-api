import SubmitButton from './SubmitButton'
import {CASSAVA_PRODUCE_TYPES, COUNTRY_INTERCROP, MAIZE_PRODUCE_TYPES, SWEET_POTATO_PRODUCE_TYPES, cassavaUnitLabel} from './constants'
import {needsSecondCrop} from './usePlaygroundForm'
import type {CassavaUnit, FormState, PriceHints} from './types'

interface Props {
    form: FormState
    cassavaUnits: CassavaUnit[]
    priceHints: PriceHints
    errors: Record<string, string>
    isLastStep: boolean
    submitting: boolean
    onChange: (patch: Partial<FormState>) => void
    onCassavaUnitChange: (kg: number) => void
    onMaizeProduceTypeChange: (type: string) => void
    onBack: () => void
    onNext: () => void
    onSubmit: () => void
}

function money(amount: number, currency: string | null): string {
    return `${currency ? currency + ' ' : ''}${amount.toLocaleString(undefined, {maximumFractionDigits: 2})}`
}

export default function StepCropYield({
    form, cassavaUnits, priceHints, errors, isLastStep, submitting,
    onChange, onCassavaUnitChange, onMaizeProduceTypeChange, onBack, onNext, onSubmit,
}: Readonly<Props>) {
    const intercropCrop = COUNTRY_INTERCROP[form.country]
    const secondCrop = needsSecondCrop(form.scenario, form.country)
    const currency = priceHints.currency
    const unitKg = Number(form.cassavaUnitWeight) || 1000

    return (
        <div className="pg-card">
            <h2 className="pg-card-title">Crop &amp; yield details</h2>
            <p className="pg-card-sub">Help the model calibrate recommendations to your actual farm conditions.</p>

            <div className="pg-form-grid pg-form-grid--3">
                <div className="pg-field">
                    <label htmlFor="fieldYield">Current cassava yield (t/ha)</label>
                    <input id="fieldYield" type="number" min="0" max="100" step="1"
                           value={form.fieldYield}
                           onChange={(e) => onChange({fieldYield: e.target.value})}
                           placeholder="e.g. 10"/>
                    {errors.fieldYield && <span className="pg-field-error">{errors.fieldYield}</span>}
                    <span className="pg-field-hint">Typical range: 5–30 t/ha</span>
                </div>

                <div className="pg-field">
                    <label htmlFor="soilQuality">
                        Soil quality
                        {form.scenario === 'IC' && intercropCrop === 'MAIZE' ? ' / maize performance' : ''}
                        {' '}(0–5)
                    </label>
                    <input id="soilQuality" type="number" min="0" max="5" step="1"
                           value={form.soilQuality}
                           onChange={(e) => onChange({soilQuality: e.target.value})}
                           placeholder="0 = poor · 5 = excellent"/>
                    {errors.soilQuality && <span className="pg-field-error">{errors.soilQuality}</span>}
                </div>

                <div className="pg-field">
                    <label htmlFor="riskAttitude">Risk preference</label>
                    <select id="riskAttitude" value={form.riskAttitude}
                            onChange={(e) => onChange({riskAttitude: e.target.value})}>
                        <option value="0">Conservative (low risk)</option>
                        <option value="1">Moderate</option>
                        <option value="2">Ambitious (high risk)</option>
                    </select>
                </div>

                <div className="pg-field">
                    <label htmlFor="cassavaProduceType">Cassava produce type</label>
                    <select id="cassavaProduceType" value={form.cassavaProduceType}
                            onChange={(e) => onChange({cassavaProduceType: e.target.value})}>
                        {CASSAVA_PRODUCE_TYPES.map((p) => (
                            <option key={p.value} value={p.value}>{p.label}</option>
                        ))}
                    </select>
                    <span className="pg-field-hint">What you sell</span>
                </div>

                {form.scenario === 'IC' && intercropCrop === 'MAIZE' && (
                    <div className="pg-field">
                        <label htmlFor="maizeProduceType">Maize produce type</label>
                        <select id="maizeProduceType" value={form.maizeProduceType}
                                onChange={(e) => onMaizeProduceTypeChange(e.target.value)}>
                            {MAIZE_PRODUCE_TYPES.map((p) => (
                                <option key={p.value} value={p.value}>{p.label}</option>
                            ))}
                        </select>
                    </div>
                )}

                {form.scenario === 'IC' && intercropCrop === 'POTATO' && (
                    <div className="pg-field">
                        <label htmlFor="sweetPotatoProduceType">Sweet potato produce type</label>
                        <select id="sweetPotatoProduceType" value={form.sweetPotatoProduceType}
                                onChange={(e) => onChange({sweetPotatoProduceType: e.target.value})}>
                            {SWEET_POTATO_PRODUCE_TYPES.map((p) => (
                                <option key={p.value} value={p.value}>{p.label}</option>
                            ))}
                        </select>
                    </div>
                )}

                {form.scenario === 'IC' && !intercropCrop && (
                    <div className="pg-field" style={{gridColumn: '1/-1'}}>
                        <span className="pg-field-hint" style={{color: 'var(--color-warning, #b45309)'}}>
                            ⚠ Select a supported country in the previous step (Nigeria or Tanzania) to configure intercropping options.
                        </span>
                    </div>
                )}
            </div>

            {/* Without a selling price extra yield is worth nothing, so nothing would ever pay */}
            <h3 className="pg-section-title">Selling prices</h3>
            <p className="pg-card-sub">
                What you are paid{currency ? ` in ${currency}` : ''}. Prefilled with the typical price in the AKILIMO
                price list; change it to your own.
            </p>
            <div className="pg-form-grid pg-form-grid--3">
                <div className="pg-field">
                    <label htmlFor="cassavaPrice">Cassava price{currency ? ` (${currency})` : ''}</label>
                    <input id="cassavaPrice" type="number" min="0" step="any" inputMode="decimal"
                           value={form.cassavaPrice}
                           onChange={(e) => onChange({cassavaPrice: e.target.value})}
                           aria-describedby="cassavaPriceHint"
                           placeholder="e.g. 15000"/>
                    {errors.cassavaPrice && <span className="pg-field-error">{errors.cassavaPrice}</span>}
                    <span className="pg-field-hint" id="cassavaPriceHint">
                        {priceHints.cassavaPerTonne
                            ? `Typical: ${money(priceHints.cassavaPerTonne * unitKg / 1000, currency)} ${cassavaUnitLabel(unitKg)}`
                            : 'No price list for this country: enter your own'}
                    </span>
                </div>

                <div className="pg-field">
                    <label htmlFor="cassavaUnit">Sold</label>
                    <select id="cassavaUnit" value={String(unitKg)}
                            onChange={(e) => onCassavaUnitChange(Number(e.target.value))}>
                        {cassavaUnits.map((u) => (
                            <option key={u.unit_weight} value={u.unit_weight}>{cassavaUnitLabel(u.unit_weight)}</option>
                        ))}
                    </select>
                    <span className="pg-field-hint">Changing it converts the price</span>
                </div>

                {secondCrop === 'MAIZE' && (
                    <div className="pg-field">
                        <label htmlFor="maizePrice">
                            Maize price{currency ? ` (${currency})` : ''}, {form.maizeProduceType === 'grain' ? 'per 50 kg bag' : 'per cob'}
                        </label>
                        <input id="maizePrice" type="number" min="0" step="any" inputMode="decimal"
                               value={form.maizePrice}
                               onChange={(e) => onChange({maizePrice: e.target.value})}/>
                        {errors.maizePrice && <span className="pg-field-error">{errors.maizePrice}</span>}
                    </div>
                )}

                {secondCrop === 'POTATO' && (
                    <div className="pg-field">
                        <label htmlFor="sweetPotatoPrice">Sweet potato price{currency ? ` (${currency})` : ''}, per tonne</label>
                        <input id="sweetPotatoPrice" type="number" min="0" step="any" inputMode="decimal"
                               value={form.sweetPotatoPrice}
                               onChange={(e) => onChange({sweetPotatoPrice: e.target.value})}/>
                        {errors.sweetPotatoPrice && <span className="pg-field-error">{errors.sweetPotatoPrice}</span>}
                    </div>
                )}
            </div>

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
