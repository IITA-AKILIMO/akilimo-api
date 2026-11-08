import { Link, router } from '@inertiajs/react'
import { useState } from 'react'
import Badge from '../../components/Badge'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import DataTable, { type Column } from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type { MaizePrice, Paginated } from '../../types'

type Filters = Record<string, string>
interface Props { items: Paginated<MaizePrice>; filters: Filters; produceTypes: string[] }

export default function MaizePricesIndex({ items, filters, produceTypes }: Props) {
    const [deleting, setDeleting] = useState<MaizePrice | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        { key: 'country', label: 'Country' },
        { key: 'produce_type', label: 'Produce Type' },
        { key: 'min_price', label: 'Min Price' },
        { key: 'max_price', label: 'Max Price' },
        { key: 'price_active', label: 'Active', render: (v) => <Badge active={!!v} /> },
    ]
    function handlePageChange(page: number) { router.get('/admin/maize-prices', { ...filters, page }, { preserveState: true }) }
    function handleDelete() {
        if (!deleting) return; setProcessing(true)
        router.delete(`/admin/maize-prices/${deleting.id}`, { onFinish: () => { setProcessing(false); setDeleting(null) } })
    }
    return (
        <AdminLayout title="Maize Prices">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <Link href="/admin/maize-prices/create" className="btn btn-success btn-sm">+ New</Link>
            </div>
            <TableFilters url="/admin/maize-prices" filters={filters} total={items.meta.total} defs={[
                { type: 'country', key: 'country' },
                { type: 'select', key: 'produce_type', label: 'Produce', options: produceTypes.map((p) => ({ value: p, label: p })) },
                { type: 'active', key: 'active' },
            ]} />
            <DataTable columns={columns} data={items.data as Record<string, unknown>[]} pagination={items.meta} links={items.links} sortBy="" onSort={() => {}} onPageChange={handlePageChange}
                actions={(row) => (<div className="d-flex gap-1 justify-content-end"><Link href={`/admin/maize-prices/${row.id}/edit`} className="btn btn-outline-secondary btn-sm">Edit</Link><button onClick={() => setDeleting(row as unknown as MaizePrice)} className="btn btn-outline-danger btn-sm">Delete</button></div>)} />
            <ConfirmDialog open={!!deleting} title="Delete maize price" message="Delete this maize price? This cannot be undone." onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing} />
        </AdminLayout>
    )
}
