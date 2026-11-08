import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import Badge from '../../components/Badge'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import DataTable, {type Column} from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type {FertilizerPrice, Paginated} from '../../types'

type Filters = Record<string, string>

interface Props {
    items: Paginated<FertilizerPrice>;
    filters: Filters
}

export default function FertilizerPricesIndex({items, filters}: Props) {
    const [deleting, setDeleting] = useState<FertilizerPrice | null>(null)
    const [processing, setProcessing] = useState(false)

    const columns: Column[] = [
        {key: 'country', label: 'Country'},
        {key: 'fertilizer_key', label: 'Fertilizer Key'},
        {key: 'min_price', label: 'Min Price'},
        {key: 'max_price', label: 'Max Price'},
        {key: 'price_per_bag', label: 'Price/Bag'},
        {key: 'price_active', label: 'Active', render: (v) => <Badge active={!!v}/>},
    ]

    function handlePageChange(page: number) {
        router.get('/admin/fertilizer-prices', {...filters, page}, {preserveState: true})
    }

    function handleDelete() {
        if (!deleting) return
        setProcessing(true)
        router.delete(`/admin/fertilizer-prices/${deleting.id}`, {
            onFinish: () => {
                setProcessing(false);
                setDeleting(null)
            },
        })
    }

    return (
        <AdminLayout title="Fertilizer Prices">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <div className="d-flex gap-2">
                    <Link href="/admin/fertilizer-prices/batch-edit" className="btn btn-outline-secondary btn-sm">Batch
                        Edit</Link>
                    <Link href="/admin/fertilizer-prices/batch-create" className="btn btn-outline-success btn-sm">Batch
                        Add</Link>
                    <Link href="/admin/fertilizer-prices/create" className="btn btn-success btn-sm">+ New</Link>
                </div>
            </div>

            <TableFilters url="/admin/fertilizer-prices" filters={filters} total={items.meta.total} defs={[
                {type: 'search', key: 'search', placeholder: 'Search fertilizer key or description…'},
                {type: 'country', key: 'country'},
                {type: 'active', key: 'active'},
            ]}/>
            <DataTable columns={columns} data={items.data as FertilizerPrice[]}
                       pagination={items.meta} links={items.links} sortBy="" onSort={() => {
            }} onPageChange={handlePageChange}
                       actions={(row) => (
                           <div className="d-flex gap-1 justify-content-end">
                               <Link href={`/admin/fertilizer-prices/${row.id}/edit`}
                                     className="btn btn-outline-secondary btn-sm">Edit</Link>
                               <button onClick={() => setDeleting(row as FertilizerPrice)}
                                       className="btn btn-outline-danger btn-sm">Delete
                               </button>
                           </div>
                       )}/>
            <ConfirmDialog open={!!deleting} title="Delete fertilizer price"
                           message="Delete this fertilizer price record? This cannot be undone."
                           onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing}/>
        </AdminLayout>
    )
}
