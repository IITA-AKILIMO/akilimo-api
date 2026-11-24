import {roundPrice} from '../lib/prices'
import {totalSteps} from '../lib/scenario'
import {validateStep} from '../lib/validation'
import type {FertilizerEntry, PriceHints, Step} from '../types'
import {useFormState} from './useFormState'
import {useReferenceData} from './useReferenceData'
import {useSubmit} from './useSubmit'

/** The playground wizard: form state, reference data and submit, put together. */
export function usePlaygroundForm() {
    const state = useFormState()
    const {form, setForm, step, setStep, setValidationErrors} = state

    // A new country means a new currency, so its prices replace any earlier ones
    function applyPriceHints(hints: PriceHints) {
        setForm({
            cassavaPrice: hints.cassavaPerTonne ? roundPrice(hints.cassavaPerTonne * Number(form.cassavaUnitWeight) / 1000) : '',
            maizePrice: (form.maizeProduceType === 'grain' ? hints.maizeGrain : hints.maizeCob)?.toString() ?? '',
            sweetPotatoPrice: hints.sweetPotatoPerTonne ? roundPrice(hints.sweetPotatoPerTonne) : '',
        })
    }

    const reference = useReferenceData(form.country, form.scenario, applyPriceHints)
    const {fertilizers, setFertilizers, priceHints} = reference
    const submission = useSubmit()

    function setMaizeProduceType(type: string) {
        const typical = type === 'grain' ? priceHints.maizeGrain : priceHints.maizeCob
        setForm({maizeProduceType: type, ...(typical ? {maizePrice: String(typical)} : {})})
    }

    function updateFert(key: string, patch: Partial<FertilizerEntry>) {
        setFertilizers((prev) => prev.map((f) => (f.key === key ? {...f, ...patch} : f)))
        setValidationErrors((prev) => {
            const next = {...prev}
            delete next[`price_${key}`]
            if (patch.selected) delete next.fertilizers
            return next
        })
    }

    function advance() {
        const errs = validateStep(step, form, fertilizers)
        if (Object.keys(errs).length) { setValidationErrors(errs); return }
        setValidationErrors({})
        setStep((prev) => (prev + 1) as Step)
    }

    function back() {
        setValidationErrors({})
        setStep((prev) => (prev - 1) as Step)
    }

    async function submit() {
        const errs = validateStep(totalSteps(form.scenario), form, fertilizers)
        if (Object.keys(errs).length) { setValidationErrors(errs); return }
        setValidationErrors({})
        await submission.send(form, fertilizers)
    }

    function reset() {
        submission.clearResult()
        state.resetForm()
        reference.resetReferenceData()
    }

    return {
        step, form, setForm,
        countries: reference.countries, cassavaUnits: reference.cassavaUnits, priceHints,
        fertilizers, loadingFerts: reference.loadingFerts,
        submitting: submission.submitting, result: submission.result, apiError: submission.apiError,
        validationErrors: state.validationErrors,
        resultRef: submission.resultRef,
        handleCountryChange: state.handleCountryChange, setCassavaUnit: state.setCassavaUnit,
        setMaizeProduceType, updateFert,
        advance, back, submit, reset,
    }
}
