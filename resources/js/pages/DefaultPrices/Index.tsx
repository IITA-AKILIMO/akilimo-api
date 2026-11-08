import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import DataTable, {type Column} from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type {DefaultPrice, Paginated} from '../../types'

type Filters = Record<string, string>

interface Props {
    items: Paginated<DefaultPrice>;
    filters: Filters
}

export default function DefaultPricesIndex({items, filters}: Props) {
    const [deleting, setDeleting] = useState<DefaultPrice | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        {key: 'country', label: 'Country'},
        {key: 'item', label: 'Item'},
        {key: 'price', label: 'Price'},
        {key: 'unit', label: 'Unit'},
        {key: 'currency', label: 'Currency'},
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
                {type: 'search', key: 'search', placeholder: 'Search item, unit or currency…'},
                {type: 'country', key: 'country'},
            ]}/>
            <DataTable columns={columns} data={items.data as DefaultPrice[]} pagination={items.meta} links={items.links}
                       sortBy="" onSort={() => {
            }} onPageChange={handlePageChange}
                       actions={(row) => (<div className="d-flex gap-1 justify-content-end">
                           <Link href={`/admin/default-prices/${row.id}/edit`}
                                 className="btn btn-outline-secondary btn-sm">
                               Edit
                           </Link>
                           <button onClick={() => setDeleting(row as DefaultPrice)}
                                   className="btn btn-outline-danger btn-sm">Delete
                           </button>
                       </div>)}/>
            <ConfirmDialog open={!!deleting} title="Delete default price"
                           message={`Delete "${deleting?.item}" for ${deleting?.country}? This cannot be undone.`}
                           onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing}/>
        </AdminLayout>
    )
}
