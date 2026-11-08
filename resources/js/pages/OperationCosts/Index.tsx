import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import Badge from '../../components/Badge'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import DataTable, {type Column} from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type {OperationCost, Paginated} from '../../types'

type Filters = Record<string, string>

interface Props {
    items: Paginated<OperationCost>;
    filters: Filters
}

export default function OperationCostsIndex({items, filters}: Props) {
    const [deleting, setDeleting] = useState<OperationCost | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        {key: 'operation_name', label: 'Operation'},
        {key: 'operation_type', label: 'Type'},
        {key: 'country_code', label: 'Country'},
        {key: 'min_cost', label: 'Min Cost'},
        {key: 'max_cost', label: 'Max Cost'},
        {key: 'is_active', label: 'Active', render: (v) => <Badge active={!!v}/>},
    ]

    function handlePageChange(page: number) {
        router.get('/admin/operation-costs', {...filters, page}, {preserveState: true})
    }

    function handleDelete() {
        if (!deleting) return;
        setProcessing(true)
        router.delete(`/admin/operation-costs/${deleting.id}`, {
            onFinish: () => {
                setProcessing(false);
                setDeleting(null)
            }
        })
    }

    return (
        <AdminLayout title="Operation Costs">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <div className="d-flex gap-2">
                    <Link href="/admin/operation-costs/batch-edit" className="btn btn-outline-secondary btn-sm">Batch
                        Edit</Link>
                    <Link href="/admin/operation-costs/batch-create" className="btn btn-outline-success btn-sm">Batch
                        Add</Link>
                    <Link href="/admin/operation-costs/create" className="btn btn-success btn-sm">+ New</Link>
                </div>
            </div>
            <TableFilters url="/admin/operation-costs" filters={filters} total={items.meta.total} defs={[
                {type: 'search', key: 'search', placeholder: 'Search operation…'},
                {type: 'country', key: 'country'},
                {type: 'active', key: 'active'},
            ]}/>
            <DataTable columns={columns} data={items.data as OperationCost[]} pagination={items.meta}
                       links={items.links} sortBy="" onSort={() => {
            }} onPageChange={handlePageChange}
                       actions={(row) => (<div className="d-flex gap-1 justify-content-end"><Link
                           href={`/admin/operation-costs/${row.id}/edit`}
                           className="btn btn-outline-secondary btn-sm">Edit</Link>
                           <button onClick={() => setDeleting(row as OperationCost)}
                                   className="btn btn-outline-danger btn-sm">Delete
                           </button>
                       </div>)}/>
            <ConfirmDialog open={!!deleting} title="Delete operation cost"
                           message={`Delete "${deleting?.operation_name}"? This cannot be undone.`}
                           onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing}/>
        </AdminLayout>
    )
}
