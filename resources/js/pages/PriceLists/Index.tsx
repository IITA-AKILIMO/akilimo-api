import { Link, router } from '@inertiajs/react'
import DataTable, { type Column } from '../../components/DataTable'
import TableFilters, { type FilterDef } from '../../components/TableFilters'
import AdminLayout from '../../layouts/AdminLayout'
import type { Paginated } from '../../types'

type Filters = Record<string, string>

interface Props {
    lists: { key: string; title: string }[]
    filters: Filters
    title: string
    searchPlaceholder: string | null
    hasMarkers: boolean
    columns: { key: string; label: string }[]
    items: Paginated<Record<string, string | number | null>>
}

export default function PriceListsIndex({ lists, filters, title, searchPlaceholder, hasMarkers, columns, items }: Props) {
    // What the farmer is shown comes first; the values behind it follow
    const tableColumns: Column[] = [
        { key: 'label', label: 'Shown to the farmer', render: (v) => <span className="fw-medium">{String(v ?? '')}</span> },
        ...columns.map((c) => ({ key: c.key, label: c.label })),
    ]

    // Which list is shown is a choice of page, not a filter: the other filters apply to it
    const defs: FilterDef[] = [{ type: 'country', key: 'country' }]
    if (searchPlaceholder) defs.unshift({ type: 'search', key: 'search', placeholder: searchPlaceholder })
    if (hasMarkers) {
        defs.push({
            type: 'select', key: 'kind', label: 'Rows',
            options: [{ value: 'choices', label: 'Price choices' }, { value: 'markers', label: 'Words only (I don’t know, exact)' }],
        })
    }

    function handlePageChange(page: number) {
        router.get('/admin/price-lists', { ...filters, page }, { preserveState: true })
    }

    function showList(list: string) {
        // The country carries over; a search or row filter belongs to one list
        router.get('/admin/price-lists', { list, country: filters.country || undefined }, { preserveState: false })
    }

    return (
        <AdminLayout title="App price lists">
            <p className="text-body-secondary" style={{ maxWidth: 760 }}>
                What the mobile app is given, worked out from the catalogue. The first column is the text a client shows for each
                choice; it maps the farmer&apos;s pick back to the value behind it and sends that to the API. These lists cannot be
                edited: change a price on <Link href="/admin/default-prices">Default prices</Link> or a rule on{' '}
                <Link href="/admin/reference">Price settings</Link> and they follow at once.
            </p>

            <ul className="nav nav-pills gap-1 mb-3" aria-label="Price lists">
                {lists.map((l) => (
                    <li className="nav-item" key={l.key}>
                        <button
                            type="button"
                            className={`nav-link py-1 px-3${filters.list === l.key ? ' active' : ''}`}
                            aria-current={filters.list === l.key ? 'page' : undefined}
                            onClick={() => showList(l.key)}
                        >
                            {l.title}
                        </button>
                    </li>
                ))}
            </ul>

            <TableFilters url="/admin/price-lists" filters={filters} total={items.meta.total} defs={defs} />

            <DataTable
                columns={tableColumns}
                data={items.data}
                pagination={items.meta}
                links={items.links}
                sortBy=""
                onSort={() => {}}
                onPageChange={handlePageChange}
            />
            <span className="visually-hidden">{title}</span>
        </AdminLayout>
    )
}
