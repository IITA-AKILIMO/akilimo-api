import type {MouseEvent} from 'react'
import Field from '../components/Field'
import StepActions from '../components/StepActions'
import {maxHarvestDate, minHarvestDate, plantingMin, typicalHarvestDate} from '../lib/dates'
import type {FormState} from '../types'

interface Props {
    form: FormState
    errors: Record<string, string>
    submitting: boolean
    onChange: (patch: Partial<FormState>) => void
    onBack: () => void
    onSubmit: () => void
}

/**
 * Open the browser's calendar when the field itself is clicked, not only its small
 * icon. Typing a date still works; browsers without showPicker() keep their own behaviour.
 */
function openPicker(e: MouseEvent<HTMLInputElement>) {
    try {
        e.currentTarget.showPicker?.()
    } catch {
        // Not allowed here (e.g. in a cross-origin frame): the icon still opens it
    }
}

export default function StepDates({form, errors, submitting, onChange, onBack, onSubmit}: Readonly<Props>) {
    function handlePlantingChange(pd: string) {
        if (!pd) { onChange({plantingDate: pd}); return }
        const minH = minHarvestDate(pd)
        const maxH = maxHarvestDate(pd)
        onChange({
            plantingDate: pd,
            // No harvest date yet: suggest a typical 12-month season
            ...(!form.harvestDate ? {harvestDate: typicalHarvestDate(pd)} : {}),
            ...(form.harvestDate && form.harvestDate < minH ? {harvestDate: minH} : {}),
            ...(form.harvestDate && form.harvestDate > maxH ? {harvestDate: maxH} : {}),
        })
    }

    return (
        <div className="card">
            <div className="card-body p-4">
                <h2 className="h5 fw-bold">Planting dates</h2>
                <p className="text-body-secondary">
                    {form.scenario === 'SPHS'
                        ? 'Set your target dates and a flexibility window — the model will find the optimal timing within that range.'
                        : 'When are you planning to plant and harvest?'}
                </p>

                <div className="row g-3">
                    <Field id="plantingDate" label="Planting date" col="col-12 col-md-6" error={errors.plantingDate}
                           hint={`No earlier than ${plantingMin}`}>
                        {(cls) => (
                            <input id="plantingDate" type="date" className={`${cls} pg-date`}
                                   value={form.plantingDate} min={plantingMin} onClick={openPicker}
                                   onChange={(e) => handlePlantingChange(e.target.value)}/>
                        )}
                    </Field>

                    <Field id="harvestDate" label="Expected harvest date" col="col-12 col-md-6" error={errors.harvestDate}
                           hint={form.plantingDate
                               ? `Between ${minHarvestDate(form.plantingDate)} and ${maxHarvestDate(form.plantingDate)}; set to 12 months after planting`
                               : 'Set automatically once you pick a planting date'}>
                        {(cls) => (
                            <input id="harvestDate" type="date" className={`${cls} pg-date`}
                                   value={form.harvestDate}
                                   min={minHarvestDate(form.plantingDate)} max={maxHarvestDate(form.plantingDate)}
                                   onClick={openPicker}
                                   onChange={(e) => onChange({harvestDate: e.target.value})}/>
                        )}
                    </Field>

                    <Field id="plantingDateWindow" label="Planting flexibility (months)" select col="col-12 col-md-6"
                           error={errors.plantingDateWindow} hint="How far before/after the planting date to explore">
                        {(cls) => (
                            <select id="plantingDateWindow" className={cls} value={form.plantingDateWindow}
                                    onChange={(e) => onChange({plantingDateWindow: e.target.value})}>
                                <option value="0">0 — exact date</option>
                                <option value="1">1 month</option>
                                <option value="2">2 months</option>
                            </select>
                        )}
                    </Field>

                    <Field id="harvestDateWindow" label="Harvest flexibility (months)" select col="col-12 col-md-6"
                           error={errors.harvestDateWindow} hint="How far before/after the harvest date to explore">
                        {(cls) => (
                            <select id="harvestDateWindow" className={cls} value={form.harvestDateWindow}
                                    onChange={(e) => onChange({harvestDateWindow: e.target.value})}>
                                <option value="0">0 — exact date</option>
                                <option value="1">1 month</option>
                                <option value="2">2 months</option>
                            </select>
                        )}
                    </Field>
                </div>

                <StepActions onBack={onBack} isLastStep submitting={submitting} onSubmit={onSubmit}/>
            </div>
        </div>
    )
}
