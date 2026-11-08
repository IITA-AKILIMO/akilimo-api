import { Link, router } from '@inertiajs/react'
import { useState } from 'react'
import Badge from '../../components/Badge'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import DataTable, { type Column } from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type { Paginated, StarchFactory } from '../../types'

type Filters = Record<string, string>
interface Props { items: Paginated<StarchFactory>; filters: Filters }

export default function StarchFactoriesIndex({ items, filters }: Props) {
    const [deleting, setDeleting] = useState<StarchFactory | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        { key: 'factory_name', label: 'Factory Name' },
        { key: 'factory_label', label: 'Label' },
        { key: 'country', label: 'Country' },
        { key: 'factory_active', label: 'Active', render: (v) => <Badge active={!!v} /> },
    ]
    function handlePageChange(page: number) { router.get('/admin/starch-factories', { ...filters, page }, { preserveState: true }) }
    function handleDelete() {
        if (!deleting) return; setProcessing(true)
        router.delete(`/admin/starch-factories/${deleting.id}`, { onFinish: () => { setProcessing(false); setDeleting(null) } })
    }
    return (
        <AdminLayout title="Starch Factories">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <Link href="/admin/starch-factories/create" className="btn btn-success btn-sm">+ New</Link>
            </div>
            <TableFilters url="/admin/starch-factories" filters={filters} total={items.meta.total} defs={[
                { type: 'search', key: 'search', placeholder: 'Search factory name or label…' },
                { type: 'country', key: 'country' },
                { type: 'active', key: 'active' },
            ]} />
            <DataTable columns={columns} data={items.data as unknown as Record<string, unknown>[]} pagination={items.meta} links={items.links} sortBy="" onSort={() => {}} onPageChange={handlePageChange}
                actions={(row) => (<div className="d-flex gap-1 justify-content-end"><Link href={`/admin/starch-factories/${row.id}/edit`} className="btn btn-outline-secondary btn-sm">Edit</Link><button onClick={() => setDeleting(row as unknown as StarchFactory)} className="btn btn-outline-danger btn-sm">Delete</button></div>)} />
            <ConfirmDialog open={!!deleting} title="Delete starch factory" message={`Delete "${deleting?.factory_name}"? This cannot be undone.`} onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing} />
        </AdminLayout>
    )
}
