import {useReducer, useState} from 'react'
import {COUNTRY_INTERCROP, INITIAL_FORM_STATE} from '../constants'
import {roundPrice} from '../lib/prices'
import type {FormState, Step} from '../types'

/** The wizard's answers, its current step and the errors shown against fields. */
export function useFormState() {
    const [step, setStep] = useState<Step>(1)
    const [form, setFormState] = useReducer(
        (prev: FormState, patch: Partial<FormState>) => ({...prev, ...patch}),
        INITIAL_FORM_STATE,
    )
    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({})

    /** Update fields and drop the errors about them, so a fixed field stops showing one. */
    function setForm(patch: Partial<FormState>) {
        setFormState(patch)
        const keys = Object.keys(patch)
        if (keys.includes('mapLat') || keys.includes('mapLong')) keys.push('mapLat', 'mapLong')
        setValidationErrors((prev) => {
            if (!keys.some((k) => k in prev)) return prev
            const next = {...prev}
            keys.forEach((k) => delete next[k])
            return next
        })
    }

    function handleCountryChange(code: string) {
        const intercropCrop = COUNTRY_INTERCROP[code]
        // The map zooms to the country, but the farm pin is the user's to place:
        // a pin in the country's centre would give advice for the wrong place.
        setForm({
            country:  code,
            mapLat:   '',
            mapLong:  '',
            ...(intercropCrop ? {intercropCrop} : {}),
        })
    }

    /** Change the cassava selling unit, keeping the same price per kg (15,000/t = 750 per 50 kg). */
    function setCassavaUnit(kg: number) {
        const oldKg = Number(form.cassavaUnitWeight) || 1000
        const price = Number(form.cassavaPrice)
        setForm({
            cassavaUnitWeight: String(kg),
            ...(price > 0 ? {cassavaPrice: roundPrice(price * kg / oldKg)} : {}),
        })
    }

    function resetForm() {
        setStep(1)
        setFormState(INITIAL_FORM_STATE)
        setValidationErrors({})
    }

    return {
        step, setStep, form, setForm, validationErrors, setValidationErrors,
        handleCountryChange, setCassavaUnit, resetForm,
    }
}
