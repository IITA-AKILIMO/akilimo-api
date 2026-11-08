import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import ConfirmDialog from '../../components/ConfirmDialog'
import DataTable, {type Column} from '../../components/DataTable'
import TableFilters from '../../components/TableFilters'
import AdminLayout from '../../layouts/AdminLayout'
import type {Crop, Paginated} from '../../types'

type Filters = Record<string, string>

interface Props {
    items: Paginated<Crop>;
    filters: Filters
}

const yesNo = (value: unknown) => (value ? 'Yes' : 'No')

export default function CropsIndex({items, filters}: Props) {
    const [deleting, setDeleting] = useState<Crop | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        {key: 'sort_order', label: '#'},
        {key: 'crop_key', label: 'Key'},
        {key: 'name', label: 'Name'},
        {key: 'active', label: 'Active', render: (value) => yesNo(value)},
        {key: 'advice_available', label: 'Advice', render: (value) => yesNo(value)},
        {key: 'countries', label: 'Countries'},
    ]

    function handlePageChange(page: number) {
        router.get('/admin/crops', {...filters, page}, {preserveState: true})
    }

    function handleDelete() {
        if (!deleting) return;
        setProcessing(true)
        router.delete(`/admin/crops/${deleting.id}`, {
            onFinish: () => {
                setProcessing(false);
                setDeleting(null)
            }
        })
    }

    return (
        <AdminLayout title="Crops">
            <div className="d-flex align-items-center justify-content-between mb-3">
                <span className="text-muted small">Advice = recommendations exist for the crop; the rest are listed for prices and information.</span>
                <Link href="/admin/crops/create" className="btn btn-success btn-sm">+ New</Link>
            </div>
            <TableFilters url="/admin/crops" filters={filters} total={items.meta.total} defs={[
                {type: 'search', key: 'search', placeholder: 'Search key, name or description…'},
                {type: 'country', key: 'country'},
                {type: 'active', key: 'active'},
                {
                    type: 'select',
                    key: 'advice',
                    label: 'Advice',
                    options: [{value: '1', label: 'Available'}, {value: '0', label: 'Not yet'}]
                },
            ]}/>
            <DataTable columns={columns} data={items.data as Crop[]}
                       pagination={items.meta} links={items.links} sortBy="" onSort={() => {
            }} onPageChange={handlePageChange}
                       actions={(row) => (
                           <div className="d-flex gap-1 justify-content-end">
                               <Link href={`/admin/crops/${row.id}/edit`}
                                     className="btn btn-outline-secondary btn-sm">
                                   Edit
                               </Link>
                               <button onClick={() => setDeleting(row as Crop)}
                                       className="btn btn-outline-danger btn-sm">Delete
                               </button>
                           </div>)}/>
            <ConfirmDialog open={!!deleting} title="Delete crop"
                           message={`Delete "${deleting?.name}"? This cannot be undone.`} onConfirm={handleDelete}
                           onCancel={() => setDeleting(null)} processing={processing}/>
        </AdminLayout>
    )
}
