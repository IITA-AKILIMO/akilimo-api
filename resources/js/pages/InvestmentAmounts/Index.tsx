import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import Badge from '../../components/Badge'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import DataTable, {type Column} from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type {InvestmentAmount, Paginated} from '../../types'

type Filters = Record<string, string>

interface Props {
    items: Paginated<InvestmentAmount>;
    filters: Filters;
    areaUnits: string[]
}

export default function InvestmentAmountsIndex({items, filters, areaUnits}: Props) {
    const [deleting, setDeleting] = useState<InvestmentAmount | null>(null)
    const [processing, setProcessing] = useState(false)
    const columns: Column[] = [
        {key: 'country', label: 'Country'},
        {key: 'investment_amount', label: 'Amount'},
        {key: 'area_unit', label: 'Area Unit'},
        {key: 'price_active', label: 'Active', render: (v) => <Badge active={!!v}/>},
    ]

    function handlePageChange(page: number) {
        router.get('/admin/investment-amounts', {...filters, page}, {preserveState: true})
    }

    function handleDelete() {
        if (!deleting) return;
        setProcessing(true)
        router.delete(`/admin/investment-amounts/${deleting.id}`, {
            onFinish: () => {
                setProcessing(false);
                setDeleting(null)
            }
        })
    }

    return (
        <AdminLayout title="Investment Amounts">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <Link href="/admin/investment-amounts/create" className="btn btn-success btn-sm">+ New</Link>
            </div>
            <TableFilters url="/admin/investment-amounts" filters={filters} total={items.meta.total} defs={[
                {type: 'country', key: 'country'},
                {
                    type: 'select',
                    key: 'area_unit',
                    label: 'Area unit',
                    options: areaUnits.map((u) => ({value: u, label: u}))
                },
                {type: 'active', key: 'active'},
            ]}/>
            <DataTable columns={columns} data={items.data as InvestmentAmount[]} pagination={items.meta}
                       links={items.links} sortBy="" onSort={() => {
            }} onPageChange={handlePageChange}
                       actions={(row) => (<div className="d-flex gap-1 justify-content-end"><Link
                           href={`/admin/investment-amounts/${row.id}/edit`}
                           className="btn btn-outline-secondary btn-sm">Edit</Link>
                           <button onClick={() => setDeleting(row as InvestmentAmount)}
                                   className="btn btn-outline-danger btn-sm">Delete
                           </button>
                       </div>)}/>
            <ConfirmDialog open={!!deleting} title="Delete investment amount"
                           message="Delete this record? This cannot be undone." onConfirm={handleDelete}
                           onCancel={() => setDeleting(null)} processing={processing}/>
        </AdminLayout>
    )
}
