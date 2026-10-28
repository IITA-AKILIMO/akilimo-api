import {Link, router} from '@inertiajs/react'
import {useState} from 'react'
import ConfirmDialog from '../../components/ConfirmDialog'
import DataTable from '../../components/DataTable'
import type {Column} from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type {ApiKey, Paginated} from '@/types'

interface Props {
    apiKeys: Paginated<ApiKey>
    filters: {
        sort_by: string
        sort_dir: 'asc' | 'desc'
        search: string
        status: string | null
    }
}

export default function ApiKeysIndex({apiKeys, filters}: Props) {
    const [deleting, setDeleting] = useState<ApiKey | null>(null)
    const [revoking, setRevoking] = useState<ApiKey | null>(null)
    const [activating, setActivating] = useState<ApiKey | null>(null)
    const [processing, setProcessing] = useState(false)
    const [searchInput, setSearchInput] = useState(filters.search)
    const [statusFilter, setStatusFilter] = useState(filters.status || '')

    const columns: Column[] = [
        {key: 'id', label: 'ID', sortable: true},
        {key: 'name', label: 'Name', sortable: true},
        {
            key: 'key_prefix',
            label: 'Key Prefix',
            render: (val) => <code className="small">{val}</code>,
        },
        {
            key: 'user',
            label: 'User',
            render: (val) => val ? (val as { name: string; email: string }).name : '—',
        },
        {
            key: 'abilities',
            label: 'Abilities',
            render: (val) => {
                const abilities = val as string[] | undefined

                if (!abilities || abilities.length === 0) {
                    return <span className="text-muted">*</span>
                }

                return (
                    <div className="d-flex flex-wrap gap-1" style={{maxWidth: '20rem'}}>
                        {abilities.map((ability) => (
                            <span key={ability} className="badge bg-info-subtle text-dark border">
                        {ability}
                    </span>
                        ))}
                    </div>
                )
            },
        },
        {
            key: 'is_active',
            label: 'Status',
            render: (val, row: Record<string, any>) => {
                const isActive = val as boolean
                const expiresAt = (row as ApiKey).expires_at
                const isExpired = expiresAt && new Date(expiresAt) < new Date()

                if (!isActive) return <span className="badge bg-secondary">Revoked</span>
                if (isExpired) return <span className="badge bg-warning text-dark">Expired</span>
                return <span className="badge bg-success">Active</span>
            },
        },
        {
            key: 'last_used_at',
            label: 'Last Used',
            sortable: true,
            render: (val) => val ? new Date(val as string).toLocaleString() : 'Never',
        },
        {
            key: 'expires_at',
            label: 'Expires',
            sortable: true,
            render: (val) => val ? new Date(val as string).toLocaleDateString() : 'Never',
        },
        {
            key: 'created_at',
            label: 'Created',
            sortable: true,
            render: (val) => val ? new Date(val as string).toLocaleDateString() : '—',
        },
    ]

    function handleSort(col: string) {
        const dir = filters.sort_by === col && filters.sort_dir === 'asc' ? 'desc' : 'asc'
        router.get('/admin/api-keys', {...filters, sort_by: col, sort_dir: dir, page: 1}, {preserveState: true})
    }

    function handlePageChange(page: number) {
        router.get('/admin/api-keys', {...filters, page}, {preserveState: true})
    }

    function navigateSearch(search: string) {
        router.get('/admin/api-keys', {...filters, search, page: 1}, {preserveState: true})
    }

    function handleStatusFilter(status: string) {
        setStatusFilter(status)
        router.get('/admin/api-keys', {...filters, status: status || null, page: 1}, {preserveState: true})
    }

    function handleDelete() {
        if (!deleting) return
        setProcessing(true)
        router.delete(`/admin/api-keys/${deleting.id}`, {
            onFinish: () => {
                setProcessing(false)
                setDeleting(null)
            },
        })
    }

    function handleRevoke() {
        if (!revoking) return
        setProcessing(true)
        router.patch(`/admin/api-keys/${revoking.id}/revoke`, {}, {
            onFinish: () => {
                setProcessing(false)
                setRevoking(null)
            },
        })
    }

    function handleActivate() {
        if (!activating) return
        setProcessing(true)
        router.patch(`/admin/api-keys/${activating.id}/activate`, {}, {
            onFinish: () => {
                setProcessing(false)
                setActivating(null)
            },
        })
    }

    return (
        <AdminLayout title="API Keys">
            <div className="d-flex align-items-center justify-content-between mb-3">
                <span className="text-muted small">
                    {apiKeys.meta.total} key{apiKeys.meta.total !== 1 ? 's' : ''} total
                </span>
                <Link href="/admin/api-keys/create" className="btn btn-success btn-sm">
                    + New API Key
                </Link>
            </div>

            <div className="row g-2 mb-3">
                <div className="col-auto">
                    <input
                        type="text"
                        className="form-control form-control-sm"
                        placeholder="Search name, prefix or user…"
                        value={searchInput}
                        onChange={(e) => setSearchInput(e.target.value)}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') navigateSearch(searchInput)
                        }}
                        onBlur={() => {
                            if (searchInput !== filters.search) navigateSearch(searchInput)
                        }}
                        style={{width: 280}}
                    />
                </div>
                {filters.search && (
                    <div className="col-auto">
                        <button
                            className="btn btn-outline-secondary btn-sm"
                            onClick={() => {
                                setSearchInput('');
                                navigateSearch('')
                            }}
                        >
                            Clear
                        </button>
                    </div>
                )}
                <div className="col-auto">
                    <select
                        className="form-select form-select-sm"
                        value={statusFilter}
                        onChange={(e) => handleStatusFilter(e.target.value)}
                        style={{width: 140}}
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Revoked</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>

            <DataTable
                columns={columns}
                data={apiKeys.data as unknown as Record<string, unknown>[]}
                pagination={apiKeys.meta}
                links={apiKeys.links}
                sortBy={filters.sort_by}
                sortDir={filters.sort_dir}
                onSort={handleSort}
                onPageChange={handlePageChange}
                actions={(row) => {
                    const key = row as ApiKey
                    const isExpired = key.expires_at && new Date(key.expires_at) < new Date()
                    return (
                        <div className="d-flex align-items-center justify-content-end gap-1">
                            <Link
                                href={`/admin/api-keys/${key.id}/edit`}
                                className="btn btn-outline-secondary btn-sm"
                            >
                                Edit
                            </Link>
                            {key.is_active && !isExpired && (
                                <button
                                    onClick={() => setRevoking(key)}
                                    className="btn btn-outline-warning btn-sm"
                                >
                                    Revoke
                                </button>
                            )}
                            {!key.is_active && (
                                <button
                                    onClick={() => setActivating(key)}
                                    className="btn btn-outline-success btn-sm"
                                >
                                    Activate
                                </button>
                            )}
                            <button
                                onClick={() => setDeleting(key)}
                                className="btn btn-outline-danger btn-sm"
                            >
                                Delete
                            </button>
                        </div>
                    )
                }}
            />

            <ConfirmDialog
                open={deleting !== null}
                title="Delete API key"
                message={`Are you sure you want to delete "${deleting?.name}"? This cannot be undone.`}
                onConfirm={handleDelete}
                onCancel={() => setDeleting(null)}
                processing={processing}
            />

            <ConfirmDialog
                open={revoking !== null}
                title="Revoke API key"
                message={`Are you sure you want to revoke "${revoking?.name}"? The key will no longer work for API requests.`}
                onConfirm={handleRevoke}
                onCancel={() => setRevoking(null)}
                processing={processing}
            />

            <ConfirmDialog
                open={activating !== null}
                title="Activate API key"
                message={`Are you sure you want to activate "${activating?.name}"?`}
                onConfirm={handleActivate}
                onCancel={() => setActivating(null)}
                processing={processing}
            />
        </AdminLayout>
    )
}
