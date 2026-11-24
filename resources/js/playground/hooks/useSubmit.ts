import {useRef, useState} from 'react'
import {computeRecommendation} from '../api'
import {buildPayload} from '../lib/payload'
import type {ApiResponse} from '../result/types'
import type {FertilizerEntry, FormState} from '../types'

/** Sends the form to /playground/compute and holds the answer (or the error) for display. */
export function useSubmit() {
    const [submitting, setSubmitting] = useState(false)
    const [result, setResult] = useState<ApiResponse | null>(null)
    const [apiError, setApiError] = useState<string | null>(null)
    const resultRef = useRef<HTMLDivElement>(null)

    async function send(form: FormState, fertilizers: FertilizerEntry[]) {
        setSubmitting(true)
        setApiError(null)
        setResult(null)

        const outcome = await computeRecommendation(buildPayload(form, fertilizers))
        if (outcome.ok) {
            setResult(outcome.data)
            setTimeout(() => resultRef.current?.scrollIntoView({behavior: 'smooth', block: 'start'}), 100)
        } else {
            setApiError(outcome.message)
        }
        setSubmitting(false)
    }

    function clearResult() {
        setResult(null)
        setApiError(null)
    }

    return {submitting, result, apiError, resultRef, send, clearResult}
}
