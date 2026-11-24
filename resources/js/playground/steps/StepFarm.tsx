import Field from '../components/Field'
import StepActions from '../components/StepActions'
import MapPicker from '../map/MapPicker'
import {AREA_UNITS, COUNTRY_INTERCROP, LANGUAGES} from '../constants'
import type {Country, FormState} from '../types'

interface Props {
    form: FormState
    countries: Country[]
    errors: Record<string, string>
    onCountryChange: (code: string) => void
    onChange: (patch: Partial<FormState>) => void
    onBack: () => void
    onNext: () => void
}

export default function StepFarm({form, countries, errors, onCountryChange, onChange, onBack, onNext}: Readonly<Props>) {
    const mapError = errors.mapLat || errors.mapLong
    const intercrop = COUNTRY_INTERCROP[form.country]

    return (
        <div className="card">
            <div className="card-body p-4">
                <h2 className="h5 fw-bold">Farm details</h2>
                <p className="text-body-secondary">Tell us about your farm location and size.</p>

                <div className="row g-3">
                    <Field id="country" label="Country" select error={errors.country}
                           hint={form.scenario === 'IC' && form.country && intercrop
                               ? `${intercrop === 'MAIZE' ? '🌾 Maize' : '🥔 Potato'} intercropping will be computed`
                               : undefined}>
                        {(cls) => (
                            <select id="country" className={cls} value={form.country}
                                    onChange={(e) => onCountryChange(e.target.value)}>
                                <option value="">Select a country…</option>
                                {countries.map((c) => <option key={c.id} value={c.code}>{c.name}</option>)}
                            </select>
                        )}
                    </Field>

                    <Field id="fieldSize" label="Field size" col="col-6 col-md-4" error={errors.fieldSize}>
                        {(cls) => (
                            <input id="fieldSize" type="number" min="0.1" step="0.1" className={cls}
                                   value={form.fieldSize} placeholder="e.g. 2.5"
                                   onChange={(e) => onChange({fieldSize: e.target.value})}/>
                        )}
                    </Field>

                    <Field id="areaUnit" label="Unit" select col="col-6 col-md-4">
                        {(cls) => (
                            <select id="areaUnit" className={cls} value={form.areaUnit}
                                    onChange={(e) => onChange({areaUnit: e.target.value})}>
                                {AREA_UNITS.map((u) => <option key={u.value} value={u.value}>{u.label}</option>)}
                            </select>
                        )}
                    </Field>

                    <Field id="lang" label="Language" select col="col-12 col-md-4">
                        {(cls) => (
                            <select id="lang" className={cls} value={form.lang}
                                    onChange={(e) => onChange({lang: e.target.value})}>
                                {LANGUAGES.map((l) => <option key={l.value} value={l.value}>{l.label}</option>)}
                            </select>
                        )}
                    </Field>

                    <div className="col-12">
                        <span className="form-label fw-semibold small d-block">Farm location</span>
                        <MapPicker
                            lat={form.mapLat}
                            lng={form.mapLong}
                            country={form.country}
                            onChange={(lat, lng) => onChange({mapLat: String(lat), mapLong: String(lng)})}
                        />
                        {mapError && <div className="invalid-feedback d-block">{mapError}</div>}
                    </div>
                </div>

                <StepActions onBack={onBack} onNext={onNext}/>
            </div>
        </div>
    )
}
