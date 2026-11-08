import { Link, router } from '@inertiajs/react'
import { useState } from 'react'
import ConfirmDialog from '../../components/ConfirmDialog'
import DataTable, { type Column } from '../../components/DataTable'
import TableFilters from '../../components/TableFilters'
import AdminLayout from '../../layouts/AdminLayout'
import type { Paginated, StarchFactory, StarchPrice } from '../../types'

type Filters = Record<string, string>
interface Props { items: Paginated<StarchPrice>; filters: Filters; factories: Pick<StarchFactory, 'id' | 'factory_name' | 'country'>[] }

export default function StarchPricesIndex({ items, filters, factories }: Props) {
    const [deleting, setDeleting] = useState<StarchPrice | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        { key: 'starch_factory_id', label: 'Factory', render: (v) => factories.find((f) => f.id === v)?.factory_name ?? String(v) },
        { key: 'price_class', label: 'Price Class' },
        { key: 'min_starch', label: 'Min Starch %' },
        { key: 'range_starch', label: 'Range' },
        { key: 'price', label: 'Price' },
        { key: 'currency', label: 'Currency' },
    ]
    function handlePageChange(page: number) { router.get('/admin/starch-prices', { ...filters, page }, { preserveState: true }) }
    function handleDelete() {
        if (!deleting) return; setProcessing(true)
        router.delete(`/admin/starch-prices/${deleting.id}`, { onFinish: () => { setProcessing(false); setDeleting(null) } })
    }
    return (
        <AdminLayout title="Starch Prices">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <div className="d-flex gap-2">
                    <Link href="/admin/starch-prices/batch-edit" className="btn btn-outline-secondary btn-sm">Batch Edit</Link>
                    <Link href="/admin/starch-prices/batch-create" className="btn btn-outline-success btn-sm">Batch Add</Link>
                    <Link href="/admin/starch-prices/create" className="btn btn-success btn-sm">+ New</Link>
                </div>
            </div>
            <TableFilters url="/admin/starch-prices" filters={filters} total={items.meta.total} defs={[
                { type: 'country', key: 'country' },
                { type: 'select', key: 'starch_factory_id', label: 'Factory', options: factories.map((f) => ({ value: String(f.id), label: `{f.factory_name} ({f.country})` })) },
            ]} />
            <DataTable columns={columns} data={items.data as Record<string, unknown>[]} pagination={items.meta} links={items.links} sortBy="" onSort={() => {}} onPageChange={handlePageChange}
                actions={(row) => (<div className="d-flex gap-1 justify-content-end"><Link href={`/admin/starch-prices/${row.id}/edit`} className="btn btn-outline-secondary btn-sm">Edit</Link><button onClick={() => setDeleting(row as unknown as StarchPrice)} className="btn btn-outline-danger btn-sm">Delete</button></div>)} />
            <ConfirmDialog open={!!deleting} title="Delete starch price" message="Delete this starch price? This cannot be undone." onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing} />
        </AdminLayout>
    )
}
