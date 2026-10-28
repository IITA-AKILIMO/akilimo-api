import { Link, router } from '@inertiajs/react'
import { useState } from 'react'
import ConfirmDialog from '../../components/ConfirmDialog'
import DataTable, { type Column } from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type { Crop, Paginated } from '../../types'

interface Props { items: Paginated<Crop> }

const yesNo = (value: unknown) => (value ? 'Yes' : 'No')

export default function CropsIndex({ items }: Props) {
    const [deleting, setDeleting] = useState<Crop | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        { key: 'sort_order', label: '#' },
        { key: 'crop_key', label: 'Key' },
        { key: 'name', label: 'Name' },
        { key: 'active', label: 'Active', render: (value) => yesNo(value) },
        { key: 'advice_available', label: 'Advice', render: (value) => yesNo(value) },
        { key: 'countries', label: 'Countries' },
    ]
    function handlePageChange(page: number) { router.get('/admin/crops', { page }, { preserveState: true }) }
    function handleDelete() {
        if (!deleting) return; setProcessing(true)
        router.delete(`/admin/crops/${deleting.id}`, { onFinish: () => { setProcessing(false); setDeleting(null) } })
    }
    return (
        <AdminLayout title="Crops">
            <div className="d-flex align-items-center justify-content-between mb-3">
                <span className="text-muted small">{items.meta.total} records. Advice = recommendations exist for the crop; the rest are listed for prices and information.</span>
                <Link href="/admin/crops/create" className="btn btn-success btn-sm">+ New</Link>
            </div>
            <DataTable columns={columns} data={items.data as unknown as Record<string, unknown>[]} pagination={items.meta} links={items.links} sortBy="" onSort={() => {}} onPageChange={handlePageChange}
                actions={(row) => (<div className="d-flex gap-1 justify-content-end"><Link href={`/admin/crops/${row.id}/edit`} className="btn btn-outline-secondary btn-sm">Edit</Link><button onClick={() => setDeleting(row as unknown as Crop)} className="btn btn-outline-danger btn-sm">Delete</button></div>)} />
            <ConfirmDialog open={!!deleting} title="Delete crop" message={`Delete "${deleting?.name}"? This cannot be undone.`} onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing} />
        </AdminLayout>
    )
}
