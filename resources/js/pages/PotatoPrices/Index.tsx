import { Link, router } from '@inertiajs/react'
import { useState } from 'react'
import Badge from '../../components/Badge'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import DataTable, { type Column } from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type { Paginated, PotatoPrice } from '../../types'

type Filters = Record<string, string>
interface Props { items: Paginated<PotatoPrice>; filters: Filters }

export default function PotatoPricesIndex({ items, filters }: Props) {
    const [deleting, setDeleting] = useState<PotatoPrice | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        { key: 'country', label: 'Country' },
        { key: 'min_price', label: 'Min Price' },
        { key: 'max_price', label: 'Max Price' },
        { key: 'min_usd', label: 'Min USD' },
        { key: 'max_usd', label: 'Max USD' },
        { key: 'price_active', label: 'Active', render: (v) => <Badge active={!!v} /> },
    ]
    function handlePageChange(page: number) { router.get('/admin/potato-prices', { ...filters, page }, { preserveState: true }) }
    function handleDelete() {
        if (!deleting) return; setProcessing(true)
        router.delete(`/admin/potato-prices/${deleting.id}`, { onFinish: () => { setProcessing(false); setDeleting(null) } })
    }
    return (
        <AdminLayout title="Potato Prices">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <Link href="/admin/potato-prices/create" className="btn btn-success btn-sm">+ New</Link>
            </div>
            <TableFilters url="/admin/potato-prices" filters={filters} total={items.meta.total} defs={[
                { type: 'country', key: 'country' },
                { type: 'active', key: 'active' },
            ]} />
            <DataTable columns={columns} data={items.data as Record<string, unknown>[]} pagination={items.meta} links={items.links} sortBy="" onSort={() => {}} onPageChange={handlePageChange}
                actions={(row) => (<div className="d-flex gap-1 justify-content-end"><Link href={`/admin/potato-prices/${row.id}/edit`} className="btn btn-outline-secondary btn-sm">Edit</Link><button onClick={() => setDeleting(row as unknown as PotatoPrice)} className="btn btn-outline-danger btn-sm">Delete</button></div>)} />
            <ConfirmDialog open={!!deleting} title="Delete potato price" message="Delete this potato price? This cannot be undone." onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing} />
        </AdminLayout>
    )
}
