import type { PriceUnit } from '../types'

interface UnitSelectProps {
    value: string
    onChange: (unit: string) => void
    units: PriceUnit[]
    error?: string
    required?: boolean
    /** A compact control for table rows */
    small?: boolean
}

/**
 * Unit picked from a fixed list instead of typed. A unit the lists do not know
 * (per_kilo, Per Bag) would give a price that no list can show.
 */
export default function UnitSelect({ value, onChange, units, error, required, small }: UnitSelectProps) {
    const known = units.some((u) => u.value === value)

    return (
        <select
            value={value}
            onChange={(e) => onChange(e.target.value)}
            className={`form-select${small ? ' form-select-sm' : ''}${error ? ' is-invalid' : ''}`}
            required={required}
        >
            <option value="">Select unit</option>
            {value && !known && <option value={value}>{value} (not in list)</option>}
            {units.map((u) => (
                <option key={u.value} value={u.value}>
                    {u.label}
                </option>
            ))}
        </select>
    )
}
