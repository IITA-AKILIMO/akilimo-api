import type { PriceCurrency } from '../types'

interface CurrencySelectProps {
    value: string
    onChange: (code: string) => void
    currencies: PriceCurrency[]
    error?: string
    /** A compact control for table rows */
    small?: boolean
}

/**
 * Currency picked from the currencies table instead of typed, so a typo cannot
 * reach a price list (an unknown currency has no rounding step).
 */
export default function CurrencySelect({ value, onChange, currencies, error, small }: CurrencySelectProps) {
    // A saved price may carry a code that is not in the list: keep it visible rather than drop it
    const known = currencies.some((c) => c.code === value)

    return (
        <select
            value={value}
            onChange={(e) => onChange(e.target.value)}
            className={`form-select${small ? ' form-select-sm' : ''}${error ? ' is-invalid' : ''}`}
        >
            <option value="">Select currency</option>
            {value && !known && <option value={value}>{value} (not in list)</option>}
            {currencies.map((c) => (
                <option key={c.code} value={c.code}>
                    {c.code} — {c.name}
                </option>
            ))}
        </select>
    )
}
