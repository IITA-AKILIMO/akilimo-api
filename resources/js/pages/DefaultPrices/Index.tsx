import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import DataTable, {type Column} from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type {DefaultPrice, Paginated, PriceStatus} from '../../types'

type Filters = Record<string, string>

interface Props {
    items: Paginated<DefaultPrice>;
    filters: Filters
    currentUserId?: number | null
}

const STATUS_STYLES: Record<PriceStatus, string> = {
    active: 'bg-success',
    draft: 'bg-secondary',
    pending_second_review: 'bg-warning text-dark',
}

const STATUS_LABELS: Record<PriceStatus, string> = {
    active: 'Active',
    draft: 'Draft',
    pending_second_review: 'Second review',
}

function StatusBadge({status}: { status: PriceStatus }) {
    return <span className={`badge ${STATUS_STYLES[status] ?? 'bg-secondary'}`}>{STATUS_LABELS[status] ?? status}</span>
}

export default function DefaultPricesIndex({items, filters, currentUserId}: Props) {
    const [deleting, setDeleting] = useState<DefaultPrice | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        {key: 'country', label: 'Country'},
        {key: 'item', label: 'Item'},
        {key: 'price', label: 'Price'},
        {key: 'unit', label: 'Unit'},
        {key: 'status', label: 'Status', render: (v) => <StatusBadge status={v as PriceStatus}/>},
        {key: 'source', label: 'Source'},
        {key: 'confidence', label: 'Confidence'},
        {key: 'effective_from', label: 'Effective'},
    ]

    function handlePageChange(page: number) {
        router.get('/admin/default-prices', {...filters, page}, {preserveState: true})
    }

    function handleDelete() {
        if (!deleting) return;
        setProcessing(true)
        router.delete(`/admin/default-prices/${deleting.id}`, {
            onFinish: () => {
                setProcessing(false);
                setDeleting(null)
            }
        })
    }

    function handleActivate(row: DefaultPrice) {
        router.post(`/admin/default-prices/${row.id}/activate`, {}, {preserveScroll: true})
    }

    function handleConfirm(row: DefaultPrice) {
        router.post(`/admin/default-prices/${row.id}/confirm`, {}, {preserveScroll: true})
    }

    return (
        <AdminLayout title="Default Prices">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <div className="d-flex gap-2">
                    <Link href="/admin/default-prices/batch-edit" className="btn btn-outline-secondary btn-sm">Batch
                        Edit</Link>
                    <Link href="/admin/default-prices/batch-create" className="btn btn-outline-success btn-sm">Batch
                        Add</Link>
                    <Link href="/admin/default-prices/create" className="btn btn-success btn-sm">+ New</Link>
                </div>
            </div>
            <TableFilters url="/admin/default-prices" filters={filters} total={items.meta.total} defs={[
                {type: 'search', key: 'search', placeholder: 'Search item, unit, currency or source…'},
                {type: 'country', key: 'country'},
                {
                    type: 'select', key: 'status', label: 'Status', options: [
                        {value: 'active', label: 'Active'},
                        {value: 'draft', label: 'Draft'},
                        {value: 'pending_second_review', label: 'Second review'},
                    ]
                },
            ]}/>
            <DataTable columns={columns} data={items.data as DefaultPrice[]} pagination={items.meta} links={items.links}
                       sortBy="" onSort={() => {
            }} onPageChange={handlePageChange}
                       actions={(row) => {
                           const price = row as DefaultPrice
                           return (<div className="d-flex gap-1 justify-content-end">
                               <Link href={`/admin/default-prices/${price.id}/edit`}
                                     className="btn btn-outline-secondary btn-sm">
                                   Edit
                               </Link>
                               {price.status === 'draft' && (
                                   <button onClick={() => handleActivate(price)}
                                           className="btn btn-outline-success btn-sm">Activate
                                   </button>
                               )}
                               {price.status === 'pending_second_review' && price.submitted_by !== currentUserId && (
                                   <button onClick={() => handleConfirm(price)}
                                           className="btn btn-outline-success btn-sm">Confirm
                                   </button>
                               )}
                               <button onClick={() => setDeleting(price)}
                                       className="btn btn-outline-danger btn-sm">Delete
                               </button>
                           </div>)
                       }}/>
            <ConfirmDialog open={!!deleting} title="Delete default price"
                           message={`Delete "${deleting?.item}" for ${deleting?.country}? This cannot be undone.`}
                           onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing}/>
        </AdminLayout>
    )
}
