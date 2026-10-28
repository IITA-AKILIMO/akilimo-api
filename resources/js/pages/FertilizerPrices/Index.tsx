import { Link, router } from '@inertiajs/react'
import { useState } from 'react'
import Badge from '../../components/Badge'
import ConfirmDialog from '../../components/ConfirmDialog'
import CountryFilter from '../../components/CountryFilter'
import DataTable, { type Column } from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type { FertilizerPrice, Paginated } from '../../types'

interface Filters { country: string; fertilizer_key: string }
interface Props { items: Paginated<FertilizerPrice>; filters: Filters }

export default function FertilizerPricesIndex({ items, filters }: Props) {
    const [deleting, setDeleting] = useState<FertilizerPrice | null>(null)
    const [processing, setProcessing] = useState(false)
    const [keyInput, setKeyInput] = useState(filters.fertilizer_key)

    const columns: Column[] = [
        { key: 'country', label: 'Country' },
        { key: 'fertilizer_key', label: 'Fertilizer Key' },
        { key: 'min_price', label: 'Min Price' },
        { key: 'max_price', label: 'Max Price' },
        { key: 'price_per_bag', label: 'Price/Bag' },
        { key: 'price_active', label: 'Active', render: (v) => <Badge active={!!v} /> },
    ]

    function navigate(next: Partial<Filters>) {
        router.get('/admin/fertilizer-prices', { ...filters, ...next, page: 1 }, { preserveState: true })
    }

    function handlePageChange(page: number) {
        router.get('/admin/fertilizer-prices', { ...filters, page }, { preserveState: true })
    }

    function handleDelete() {
        if (!deleting) return
        setProcessing(true)
        router.delete(`/admin/fertilizer-prices/${deleting.id}`, {
            onFinish: () => { setProcessing(false); setDeleting(null) },
        })
    }

    return (
        <AdminLayout title="Fertilizer Prices">
            <div className="d-flex align-items-center justify-content-between mb-3">
                <span className="text-muted small">{items.meta.total} records</span>
                <div className="d-flex gap-2">
                    <Link href="/admin/fertilizer-prices/batch-edit" className="btn btn-outline-secondary btn-sm">Batch Edit</Link>
                    <Link href="/admin/fertilizer-prices/batch-create" className="btn btn-outline-success btn-sm">Batch Add</Link>
                    <Link href="/admin/fertilizer-prices/create" className="btn btn-success btn-sm">+ New</Link>
                </div>
            </div>

            <div className="row g-2 mb-3">
                <div className="col-auto">
                    <CountryFilter value={filters.country} onChange={(v) => navigate({ country: v })} />
                </div>
                <div className="col-auto">
                    <input
                        type="text" className="form-control form-control-sm" placeholder="Fertilizer key…"
                        value={keyInput} onChange={(e) => setKeyInput(e.target.value)}
                        onKeyDown={(e) => { if (e.key === 'Enter') navigate({ fertilizer_key: keyInput }) }}
                        onBlur={() => navigate({ fertilizer_key: keyInput })}
                        style={{ width: 160 }}
                    />
                </div>
                {(filters.country || filters.fertilizer_key) && (
                    <div className="col-auto">
                        <button className="btn btn-outline-secondary btn-sm" onClick={() => { setKeyInput(''); navigate({ country: '', fertilizer_key: '' }) }}>Clear</button>
                    </div>
                )}
            </div>

            <DataTable columns={columns} data={items.data as FertilizerPrice[]}
                pagination={items.meta} links={items.links} sortBy="" onSort={() => {}} onPageChange={handlePageChange}
                actions={(row) => (
                    <div className="d-flex gap-1 justify-content-end">
                        <Link href={`/admin/fertilizer-prices/${row.id}/edit`} className="btn btn-outline-secondary btn-sm">Edit</Link>
                        <button onClick={() => setDeleting(row as unknown as FertilizerPrice)} className="btn btn-outline-danger btn-sm">Delete</button>
                    </div>
                )} />
            <ConfirmDialog open={!!deleting} title="Delete fertilizer price"
                message="Delete this fertilizer price record? This cannot be undone."
                onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing} />
        </AdminLayout>
    )
}
