import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import Badge from '../../components/Badge'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import DataTable, {type Column} from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type {Fertilizer, Paginated} from '../../types'

type Filters = Record<string, string>

interface Props {
    items: Paginated<Fertilizer>;
    filters: Filters;
    useCases: string[]
}

export default function FertilizersIndex({items, filters, useCases}: Props) {
    const [deleting, setDeleting] = useState<Fertilizer | null>(null)
    const [processing, setProcessing] = useState(false)

    const columns: Column[] = [
        {key: 'name', label: 'Name', sortable: false},
        {key: 'fertilizer_key', label: 'Key', sortable: false},
        {key: 'type', label: 'Type', sortable: false},
        {key: 'country', label: 'Country', sortable: false},
        {key: 'use_case', label: 'Use Case', sortable: false},
        {key: 'weight', label: 'Weight (kg)', sortable: false},
        {key: 'available', label: 'Available', sortable: false, render: (v) => <Badge active={!!v}/>},
    ]


    function handlePageChange(page: number) {
        router.get('/admin/fertilizers', {...filters, page}, {preserveState: true})
    }

    function handleDelete() {
        if (!deleting) return
        setProcessing(true)
        router.delete(`/admin/fertilizers/${deleting.id}`, {
            onFinish: () => {
                setProcessing(false);
                setDeleting(null)
            },
        })
    }

    return (
        <AdminLayout title="Fertilizers">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <Link href="/admin/fertilizers/create" className="btn btn-success btn-sm">+ New</Link>
            </div>

            <TableFilters url="/admin/fertilizers" filters={filters} total={items.meta.total} defs={[
                {type: 'search', key: 'search', placeholder: 'Search name, type or key…'},
                {type: 'country', key: 'country'},
                {
                    type: 'select',
                    key: 'use_case',
                    label: 'Use case',
                    options: useCases.map((u) => ({value: u, label: u}))
                },
                {
                    type: 'select',
                    key: 'available',
                    label: 'Availability',
                    options: [{value: '1', label: 'Available'}, {value: '0', label: 'Unavailable'}]
                },
            ]}/>
            <DataTable columns={columns} data={items.data as Fertilizer[]}
                       pagination={items.meta} links={items.links} sortBy="" onSort={() => {
            }} onPageChange={handlePageChange}
                       actions={(row) => (
                           <div className="d-flex gap-1 justify-content-end">
                               <Link href={`/admin/fertilizers/${row.id}/edit`}
                                     className="btn btn-outline-secondary btn-sm">
                                   Edit
                               </Link>
                               <button onClick={() => setDeleting(row as Fertilizer)}
                                       className="btn btn-outline-danger btn-sm">Delete
                               </button>
                           </div>
                       )}/>
            <ConfirmDialog open={!!deleting} title="Delete fertilizer"
                           message={`Delete "${deleting?.name}"? This cannot be undone.`}
                           onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing}/>
        </AdminLayout>
    )
}
