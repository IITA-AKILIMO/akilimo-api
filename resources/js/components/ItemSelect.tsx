import type { PriceItem } from '../types'

interface ItemSelectProps {
    value: string
    onChange: (item: string) => void
    items: PriceItem[]
    error?: string
    required?: boolean
    /** A compact control for table rows */
    small?: boolean
}

/**
 * Item picked from the price_items table instead of typed, so a price cannot be
 * saved for an item the price lists do not know. A new item is added to the
 * table first.
 */
export default function ItemSelect({ value, onChange, items, error, required, small }: ItemSelectProps) {
    const known = items.some((i) => i.item === value)

    return (
        <select
            value={value}
            onChange={(e) => onChange(e.target.value)}
            className={`form-select${small ? ' form-select-sm' : ''}${error ? ' is-invalid' : ''}`}
            required={required}
        >
            <option value="">Select item</option>
            {value && !known && <option value={value}>{value} (not in list)</option>}
            {items.map((i) => (
                <option key={i.item} value={i.item}>
                    {i.item}
                </option>
            ))}
        </select>
    )
}
