import Field from '../components/Field'
import StepActions from '../components/StepActions'
import {CASSAVA_PRODUCE_TYPES, COUNTRY_INTERCROP, MAIZE_PRODUCE_TYPES, SWEET_POTATO_PRODUCE_TYPES, cassavaUnitLabel} from '../constants'
import {needsSecondCrop} from '../lib/scenario'
import type {CassavaUnit, FormState, PriceHints} from '../types'

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
        <div className="card">
            <div className="card-body p-4">
                <h2 className="h5 fw-bold">Crop &amp; yield details</h2>
                <p className="text-body-secondary">Help the model calibrate recommendations to your actual farm conditions.</p>

                <div className="row g-3">
                    <Field id="fieldYield" label="Current cassava yield (t/ha)" col="col-12 col-md-4"
                           error={errors.fieldYield} hint="Typical range: 5–30 t/ha">
                        {(cls) => (
                            <input id="fieldYield" type="number" min="0" max="100" step="1" className={cls}
                                   value={form.fieldYield} placeholder="e.g. 10"
                                   onChange={(e) => onChange({fieldYield: e.target.value})}/>
                        )}
                    </Field>

                    <Field id="soilQuality" col="col-12 col-md-4" error={errors.soilQuality}
                           label={`Soil quality${form.scenario === 'IC' && intercropCrop === 'MAIZE' ? ' / maize performance' : ''} (0–5)`}>
                        {(cls) => (
                            <input id="soilQuality" type="number" min="0" max="5" step="1" className={cls}
                                   value={form.soilQuality} placeholder="0 = poor · 5 = excellent"
                                   onChange={(e) => onChange({soilQuality: e.target.value})}/>
                        )}
                    </Field>

                    <Field id="riskAttitude" label="Risk preference" select col="col-12 col-md-4">
                        {(cls) => (
                            <select id="riskAttitude" className={cls} value={form.riskAttitude}
                                    onChange={(e) => onChange({riskAttitude: e.target.value})}>
                                <option value="0">Conservative (low risk)</option>
                                <option value="1">Moderate</option>
                                <option value="2">Ambitious (high risk)</option>
                            </select>
                        )}
                    </Field>

                    <Field id="cassavaProduceType" label="Cassava produce type" select col="col-12 col-md-4"
                           hint="What you sell">
                        {(cls) => (
                            <select id="cassavaProduceType" className={cls} value={form.cassavaProduceType}
                                    onChange={(e) => onChange({cassavaProduceType: e.target.value})}>
                                {CASSAVA_PRODUCE_TYPES.map((p) => <option key={p.value} value={p.value}>{p.label}</option>)}
                            </select>
                        )}
                    </Field>

                    {form.scenario === 'IC' && intercropCrop === 'MAIZE' && (
                        <Field id="maizeProduceType" label="Maize produce type" select col="col-12 col-md-4">
                            {(cls) => (
                                <select id="maizeProduceType" className={cls} value={form.maizeProduceType}
                                        onChange={(e) => onMaizeProduceTypeChange(e.target.value)}>
                                    {MAIZE_PRODUCE_TYPES.map((p) => <option key={p.value} value={p.value}>{p.label}</option>)}
                                </select>
                            )}
                        </Field>
                    )}

                    {form.scenario === 'IC' && intercropCrop === 'POTATO' && (
                        <Field id="sweetPotatoProduceType" label="Sweet potato produce type" select col="col-12 col-md-4">
                            {(cls) => (
                                <select id="sweetPotatoProduceType" className={cls} value={form.sweetPotatoProduceType}
                                        onChange={(e) => onChange({sweetPotatoProduceType: e.target.value})}>
                                    {SWEET_POTATO_PRODUCE_TYPES.map((p) => <option key={p.value} value={p.value}>{p.label}</option>)}
                                </select>
                            )}
                        </Field>
                    )}

                    {form.scenario === 'IC' && !intercropCrop && (
                        <div className="col-12">
                            <div className="alert alert-warning mb-0 py-2 small" role="alert">
                                ⚠ Select a supported country in the previous step (Nigeria or Tanzania) to configure intercropping options.
                            </div>
                        </div>
                    )}
                </div>

                {/* Without a selling price extra yield is worth nothing, so nothing would ever pay */}
                <h3 className="h6 fw-bold mt-4">Selling prices</h3>
                <p className="text-body-secondary small">
                    What you are paid{currency ? ` in ${currency}` : ''}. Prefilled with the typical price in the AKILIMO
                    price list; change it to your own.
                </p>
                <div className="row g-3">
                    <Field id="cassavaPrice" label={`Cassava price${currency ? ` (${currency})` : ''}`} col="col-12 col-md-4"
                           error={errors.cassavaPrice}
                           hint={priceHints.cassavaPerTonne
                               ? `Typical: ${money(priceHints.cassavaPerTonne * unitKg / 1000, currency)} ${cassavaUnitLabel(unitKg)}`
                               : 'No price list for this country: enter your own'}>
                        {(cls) => (
                            <input id="cassavaPrice" type="number" min="0" step="any" inputMode="decimal" className={cls}
                                   value={form.cassavaPrice} placeholder="e.g. 15000"
                                   onChange={(e) => onChange({cassavaPrice: e.target.value})}/>
                        )}
                    </Field>

                    <Field id="cassavaUnit" label="Sold" select col="col-12 col-md-4" hint="Changing it converts the price">
                        {(cls) => (
                            <select id="cassavaUnit" className={cls} value={String(unitKg)}
                                    onChange={(e) => onCassavaUnitChange(Number(e.target.value))}>
                                {cassavaUnits.map((u) => (
                                    <option key={u.unit_weight} value={u.unit_weight}>{cassavaUnitLabel(u.unit_weight)}</option>
                                ))}
                            </select>
                        )}
                    </Field>

                    {secondCrop === 'MAIZE' && (
                        <Field id="maizePrice" col="col-12 col-md-4" error={errors.maizePrice}
                               label={`Maize price${currency ? ` (${currency})` : ''}, ${form.maizeProduceType === 'grain' ? 'per 50 kg bag' : 'per cob'}`}>
                            {(cls) => (
                                <input id="maizePrice" type="number" min="0" step="any" inputMode="decimal" className={cls}
                                       value={form.maizePrice}
                                       onChange={(e) => onChange({maizePrice: e.target.value})}/>
                            )}
                        </Field>
                    )}

                    {secondCrop === 'POTATO' && (
                        <Field id="sweetPotatoPrice" col="col-12 col-md-4" error={errors.sweetPotatoPrice}
                               label={`Sweet potato price${currency ? ` (${currency})` : ''}, per tonne`}>
                            {(cls) => (
                                <input id="sweetPotatoPrice" type="number" min="0" step="any" inputMode="decimal" className={cls}
                                       value={form.sweetPotatoPrice}
                                       onChange={(e) => onChange({sweetPotatoPrice: e.target.value})}/>
                            )}
                        </Field>
                    )}
                </div>

                <StepActions onBack={onBack} isLastStep={isLastStep} submitting={submitting}
                             onNext={onNext} onSubmit={onSubmit}/>
            </div>
        </div>
    )
}
