import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import DataTable, {type Column} from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type {Currency, Paginated} from '../../types'

type Filters = Record<string, string>

interface Props {
    items: Paginated<Currency>;
    filters: Filters
}

export default function CurrenciesIndex({items, filters}: Props) {
    const [deleting, setDeleting] = useState<Currency | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        {key: 'country_code', label: 'Code'},
        {key: 'country', label: 'Country'},
        {key: 'currency_name', label: 'Currency'},
        {key: 'currency_code', label: 'ISO Code'},
        {key: 'currency_symbol', label: 'Symbol'},
    ]

    function handlePageChange(page: number) {
        router.get('/admin/currencies', {...filters, page}, {preserveState: true})
    }

    function handleDelete() {
        if (!deleting) return;
        setProcessing(true)
        router.delete(`/admin/currencies/${deleting.id}`, {
            onFinish: () => {
                setProcessing(false);
                setDeleting(null)
            }
        })
    }

    return (
        <AdminLayout title="Currencies">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <Link href="/admin/currencies/create" className="btn btn-success btn-sm">+ New</Link>
            </div>
            <TableFilters url="/admin/currencies" filters={filters} total={items.meta.total} defs={[
                {type: 'search', key: 'search', placeholder: 'Search currency or country…'},
                {type: 'country', key: 'country'},
            ]}/>
            <DataTable columns={columns} data={items.data as Currency[]} pagination={items.meta} links={items.links}
                       sortBy="" onSort={() => {
            }} onPageChange={handlePageChange}
                       actions={(row) => (<div className="d-flex gap-1 justify-content-end"><Link
                           href={`/admin/currencies/${row.id}/edit`}
                           className="btn btn-outline-secondary btn-sm">Edit</Link>
                           <button onClick={() => setDeleting(row as Currency)}
                                   className="btn btn-outline-danger btn-sm">Delete
                           </button>
                       </div>)}/>
            <ConfirmDialog open={!!deleting} title="Delete currency"
                           message={`Delete "${deleting?.currency_name}"? This cannot be undone.`}
                           onConfirm={handleDelete} onCancel={() => setDeleting(null)} processing={processing}/>
        </AdminLayout>
    )
}
