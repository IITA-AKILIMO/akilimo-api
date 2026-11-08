import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import ConfirmDialog from '../../components/ConfirmDialog'
import DataTable from '../../components/DataTable'
import type {Column} from '../../components/DataTable'
import TableFilters from '../../components/TableFilters'
import AdminLayout from '../../layouts/AdminLayout'
import type {Paginated, User} from '../../types'

interface Props {
    users: Paginated<User>
    filters: {
        sort_by: string
        sort_dir: 'asc' | 'desc'
        search: string
        role: string
    }
}

export default function UsersIndex({users, filters}: Props) {
    const [deleting, setDeleting] = useState<User | null>(null)
    const [processing, setProcessing] = useState(false)

    const ROLE_BADGE: Record<string, string> = {
        admin: 'bg-danger',
        partner: 'bg-primary',
        user: 'bg-secondary',
    }

    const columns: Column[] = [
        {key: 'id', label: 'ID', sortable: true},
        {key: 'name', label: 'Name', sortable: true},
        {key: 'username', label: 'Username', sortable: true},
        {key: 'email', label: 'Email', sortable: true},
        {
            key: 'role',
            label: 'Role',
            sortable: false,
            render: (val) => {
                const role = (val as string) ?? 'user'
                return (
                    <span className={`badge ${ROLE_BADGE[role] ?? 'bg-secondary'}`}>
                        {role}
                    </span>
                )
            },
        },
        {
            key: 'created_at',
            label: 'Created',
            sortable: true,
            render: (val) =>
                val ? new Date(val as string).toLocaleDateString() : '—',
        },
    ]

    function handleSort(col: string) {
        const dir =
            filters.sort_by === col && filters.sort_dir === 'asc' ? 'desc' : 'asc'
        router.get('/admin/users', {...filters, sort_by: col, sort_dir: dir, page: 1}, {preserveState: true})
    }

    function handlePageChange(page: number) {
        router.get('/admin/users', {...filters, page}, {preserveState: true})
    }

    function handleDelete() {
        if (!deleting) return
        setProcessing(true)
        router.delete(`/admin/users/${deleting.id}`, {
            onFinish: () => {
                setProcessing(false)
                setDeleting(null)
            },
        })
    }

    return (
        <AdminLayout title="Users">
            <div className="d-flex align-items-center justify-content-end mb-3">
                <Link href="/admin/users/create" className="btn btn-success btn-sm">
                    + New User
                </Link>
            </div>

            <TableFilters url="/admin/users" filters={filters} total={users.meta.total} defs={[
                {type: 'search', key: 'search', placeholder: 'Search name, username or email…'},
                {
                    type: 'select', key: 'role', label: 'Role', options: [
                        {value: 'admin', label: 'Admin'},
                        {value: 'partner', label: 'Partner'},
                        {value: 'user', label: 'User'},
                    ]
                },
            ]}/>

            <DataTable
                columns={columns}
                data={users.data as User[]}
                pagination={users.meta}
                links={users.links}
                sortBy={filters.sort_by}
                sortDir={filters.sort_dir}
                onSort={handleSort}
                onPageChange={handlePageChange}
                actions={(row) => (
                    <div className="d-flex align-items-center justify-content-end gap-1">
                        <Link
                            href={`/admin/users/${row.id}/edit`}
                            className="btn btn-outline-secondary btn-sm"
                        >
                            Edit
                        </Link>
                        <button
                            onClick={() => setDeleting(row as User)}
                            className="btn btn-outline-danger btn-sm"
                        >
                            Delete
                        </button>
                    </div>
                )}
            />

            <ConfirmDialog
                open={deleting !== null}
                title="Delete user"
                message={`Are you sure you want to delete "${deleting?.name}"? This cannot be undone.`}
                onConfirm={handleDelete}
                onCancel={() => setDeleting(null)}
                processing={processing}
            />
        </AdminLayout>
    )
}
