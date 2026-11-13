import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import Badge from '../../components/Badge'
import ConfirmDialog from '../../components/ConfirmDialog'
import DataTable, {type Column} from '../../components/DataTable'
import TableFilters from '../../components/TableFilters'
import AdminLayout from '../../layouts/AdminLayout'
import type {Country, Paginated} from '../../types'

type Filters = Record<string, string>

interface Props {
    items: Paginated<Country>;
    filters: Filters
}

export default function CountriesIndex({items, filters}: Props) {
    const [deleting, setDeleting] = useState<Country | null>(null)
    const [processing, setProcessing] = useState(false)

    const columns: Column[] = [
        {key: 'sort_order', label: 'Order'},
        {key: 'code', label: 'Code'},
        {key: 'name', label: 'Country Name'},
        {key: 'currency_code', label: 'Currency'},
        {
            key: 'default_price_basis',
            label: 'Price basis',
            render: (v) => v === 'subsidised' ? 'Subsidised' : 'Open market',
        },
        {key: 'active', label: 'Active', render: (v) => <Badge active={!!v}/>},
        {
            key: 'has_boundary',
            label: 'Boundary',
            render: (v) => <Badge active={!!v} activeLabel="Set" inactiveLabel="None"/>
        },
    ]

    function handlePageChange(page: number) {
        router.get('/admin/countries', {...filters, page}, {preserveState: true})
    }

    function handleDelete() {
        if (!deleting) return
        setProcessing(true)
        router.delete(`/admin/countries/${deleting.id}`, {
            onFinish: () => {
                setProcessing(false);
                setDeleting(null)
            },
        })
    }

    return (
        <AdminLayout title="Countries">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <Link href="/admin/countries/create" className="btn btn-success btn-sm">+ New</Link>
            </div>
            <TableFilters url="/admin/countries" filters={filters} total={items.meta.total} defs={[
                {type: 'search', key: 'search', placeholder: 'Search code or name…'},
                {type: 'active', key: 'active'},
            ]}/>
            <DataTable
                columns={columns}
                data={items.data as Country[]}
                pagination={items.meta}
                links={items.links}
                sortBy=""
                onSort={() => {
                }}
                onPageChange={handlePageChange}
                actions={(row) => (
                    <div className="d-flex gap-1 justify-content-end">
                        <Link href={`/admin/countries/${row.id}/edit`} className="btn btn-outline-secondary btn-sm">
                            Edit
                        </Link>
                        <button onClick={() => setDeleting(row as Country)}
                                className="btn btn-outline-danger btn-sm">Delete
                        </button>
                    </div>
                )}
            />
            <ConfirmDialog
                open={!!deleting}
                title="Delete country"
                message={`Delete "${deleting?.name}" (${deleting?.code})? This cannot be undone.`}
                onConfirm={handleDelete}
                onCancel={() => setDeleting(null)}
                processing={processing}
            />
        </AdminLayout>
    )
}
