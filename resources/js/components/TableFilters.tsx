import { router } from '@inertiajs/react'
import { useEffect, useRef, useState } from 'react'
import CountryFilter from './CountryFilter'

/**
 * One filter bar for every admin table: a search box, the country list,
 * Active / Inactive, and any other choices a table needs. Filters live in the
 * page address, so a filtered view can be reloaded, bookmarked and shared.
 */

export type FilterDef =
    | { type: 'search'; key: string; placeholder: string }
    | { type: 'country'; key: string }
    | { type: 'active'; key: string; label?: string }
    | { type: 'select'; key: string; label: string; options: { value: string; label: string }[] }

interface TableFiltersProps {
    /** The index page's address, e.g. /admin/cassava-prices */
    url: string
    /** Current values, as the controller echoed them */
    filters: Record<string, string | undefined>
    defs: FilterDef[]
    /** Shown on the right, e.g. "23 records" */
    total?: number
}

const SEARCH_DELAY_MS = 400

export default function TableFilters({ url, filters, defs, total }: TableFiltersProps) {
    const searchDef = defs.find((d) => d.type === 'search')
    const [term, setTerm] = useState(searchDef ? filters[searchDef.key] ?? '' : '')
    const timer = useRef<ReturnType<typeof setTimeout>>(undefined)

    // Keep the box in step when the page changes the filters (Clear, back button)
    useEffect(() => {
        if (searchDef) setTerm(filters[searchDef.key] ?? '')
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [searchDef?.key, filters[searchDef?.key ?? '']])

    function apply(next: Record<string, string>) {
        const params: Record<string, string> = {}
        for (const [k, v] of Object.entries({ ...filters, ...next })) {
            if (v !== undefined && v !== '') params[k] = v
        }
        router.get(url, params, { preserveState: true, preserveScroll: true, replace: true })
    }

    function onSearch(value: string) {
        setTerm(value)
        clearTimeout(timer.current)
        timer.current = setTimeout(() => apply({ [searchDef!.key]: value.trim() }), SEARCH_DELAY_MS)
    }

    const activeCount = defs.filter((d) => (filters[d.key] ?? '') !== '').length

    function clearAll() {
        clearTimeout(timer.current)
        setTerm('')
        apply(Object.fromEntries(defs.map((d) => [d.key, ''])))
    }

    return (
        <div className="table-filters d-flex flex-wrap align-items-center gap-2 mb-3" role="search" aria-label="Filter the table">
            {defs.map((d) => {
                const id = `filter-${d.key}`
                switch (d.type) {
                    case 'search':
                        return (
                            <div key={d.key} className="table-filters-search">
                                <label htmlFor={id} className="visually-hidden">{d.placeholder}</label>
                                <input
                                    id={id}
                                    type="search"
                                    className="form-control form-control-sm"
                                    placeholder={d.placeholder}
                                    value={term}
                                    onChange={(e) => onSearch(e.target.value)}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') { clearTimeout(timer.current); apply({ [d.key]: term.trim() }) }
                                    }}
                                />
                            </div>
                        )
                    case 'country':
                        return <CountryFilter key={d.key} value={filters[d.key] ?? ''} onChange={(v) => apply({ [d.key]: v })} />
                    case 'active':
                        return (
                            <div key={d.key} className="d-flex align-items-center gap-2">
                                <label htmlFor={id} className="form-label mb-0 fw-medium text-nowrap">{d.label ?? 'Status'}</label>
                                <select id={id} className="form-select form-select-sm w-auto" value={filters[d.key] ?? ''}
                                    onChange={(e) => apply({ [d.key]: e.target.value })}>
                                    <option value="">All</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        )
                    case 'select':
                        return (
                            <div key={d.key} className="d-flex align-items-center gap-2">
                                <label htmlFor={id} className="form-label mb-0 fw-medium text-nowrap">{d.label}</label>
                                <select id={id} className="form-select form-select-sm w-auto" value={filters[d.key] ?? ''}
                                    onChange={(e) => apply({ [d.key]: e.target.value })}>
                                    <option value="">All</option>
                                    {d.options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                                </select>
                            </div>
                        )
                }
            })}

            {activeCount > 0 && (
                <button type="button" className="btn btn-outline-secondary btn-sm" onClick={clearAll}>
                    Clear filters ({activeCount})
                </button>
            )}

            {total !== undefined && (
                <span className="text-body-secondary small ms-auto" role="status" aria-live="polite">
                    {total.toLocaleString()} {total === 1 ? 'record' : 'records'}{activeCount > 0 ? ' match' : ''}
                </span>
            )}
        </div>
    )
}
