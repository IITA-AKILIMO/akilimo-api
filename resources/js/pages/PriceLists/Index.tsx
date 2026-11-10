import { Link, router } from '@inertiajs/react'
import CountrySelect from '../../components/CountrySelect'
import AdminLayout from '../../layouts/AdminLayout'

interface Props {
    lists: { key: string; title: string }[]
    list: string
    country: string
    title: string
    columns: { key: string; label: string }[]
    rows: Record<string, string | number | null>[]
    total: number
    limit: number
}

export default function PriceListsIndex({ lists, list, country, title, columns, rows, total, limit }: Props) {
    function go(next: { list?: string; country?: string }) {
        router.get('/admin/price-lists', { list, country, ...next }, { preserveState: true })
    }

    return (
        <AdminLayout title="App price lists">
            <p className="text-body-secondary" style={{ maxWidth: 760 }}>
                What the mobile app is given, worked out from the catalogue. These lists cannot be edited: change a price on{' '}
                <Link href="/admin/default-prices">Default prices</Link> or a rule on <Link href="/admin/reference">Price settings</Link> and they follow at once.
            </p>

            <div className="d-flex flex-wrap gap-3 align-items-end mb-3">
                <div>
                    <label className="form-label small mb-1">List</label>
                    <select className="form-select" value={list} onChange={(e) => go({ list: e.target.value })}>
                        {lists.map((l) => <option key={l.key} value={l.key}>{l.title}</option>)}
                    </select>
                </div>
                <div style={{ minWidth: 220 }}>
                    <label className="form-label small mb-1">Country</label>
                    <div className="d-flex gap-2">
                        <div className="flex-grow-1"><CountrySelect value={country} onChange={(c) => go({ country: c })} /></div>
                        {country && <button className="btn btn-outline-secondary" onClick={() => go({ country: '' })}>All</button>}
                    </div>
                </div>
                <div className="text-body-secondary small ms-auto">
                    {total} row{total === 1 ? '' : 's'}{total > limit ? ` (showing the first ${limit})` : ''}
                </div>
            </div>

            <div className="card border-0 shadow-sm">
                <div className="table-responsive">
                    <table className="table table-sm table-hover align-middle mb-0">
                        <caption className="visually-hidden">{title}</caption>
                        <thead className="table-light">
                            <tr>{columns.map((c) => <th key={c.key}>{c.label}</th>)}</tr>
                        </thead>
                        <tbody>
                            {rows.length === 0 && (
                                <tr><td colSpan={columns.length} className="text-center text-body-secondary py-4">
                                    Nothing here: no active catalogue price feeds this list{country ? ' in this country' : ''}.
                                </td></tr>
                            )}
                            {rows.map((row, i) => (
                                <tr key={i}>
                                    {columns.map((c) => <td key={c.key}>{row[c.key] === null || row[c.key] === undefined ? '' : String(row[c.key])}</td>)}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    )
}
