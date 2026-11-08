import { Link, router } from '@inertiajs/react'
import { useState } from 'react'
import DataTable from '../../components/DataTable'
import type { Column } from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type { ApiRequest, Paginated } from '../../types'

interface Props {
    requests: Paginated<ApiRequest>
    filters: {
        country: string
        use_case: string
        excluded: string
        date_from: string
        date_to: string
        search: string
        sort_by: string
        sort_dir: 'asc' | 'desc'
    }
    use_cases: string[]
}

const USE_CASE_COLORS: Record<string, string> = {
    FR: 'success',
    PP: 'primary',
    IC: 'info',
    SPHS: 'warning',
    NA: 'secondary',
}

function navigate(path: string, params: Record<string, any>) {
    router.get(path, params, { preserveScroll: true, replace: true })
}

export default function RequestsIndex({ requests, filters, use_cases }: Props) {
    const [search, setSearch] = useState(filters.search)

    const columns: Column[] = [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'device_token', label: 'Device', sortable: false, render: (v) => v ? <code className="small">{v as string}</code> : '—' },
        { key: 'country_code', label: 'Country', sortable: true, render: (v) => v ? <span className="badge bg-light text-dark border">{v as string}</span> : '—' },
        {
            key: 'use_case', label: 'Use Case', sortable: true,
            render: (v) => v
                ? <span className={`badge bg-${USE_CASE_COLORS[v as string] ?? 'secondary'}`}>{v as string}</span>
                : '—',
        },
        {
            key: 'excluded', label: 'Excluded', sortable: false,
            render: (v) => v
                ? <span className="badge bg-danger">Yes</span>
                : <span className="badge bg-success">No</span>,
        },
        {
            key: 'duration_ms', label: 'Duration', sortable: true,
            render: (v) => v != null ? `${v}ms` : '—',
        },
        {
            key: 'created_at', label: 'Date', sortable: true,
            render: (v) => v ? new Date(v as string).toLocaleString() : '—',
        },
    ]

    function applyFilters(overrides: Partial<typeof filters>) {
        navigate('/admin/requests', { ...filters, ...overrides, page: 1 })
    }

    function handleSort(col: string) {
        const dir = filters.sort_by === col && filters.sort_dir === 'asc' ? 'desc' : 'asc'
        navigate('/admin/requests', { ...filters, sort_by: col, sort_dir: dir, page: 1 })
    }

    function handlePageChange(page: number) {
        navigate('/admin/requests', { ...filters, page })
    }

    const hasFilters = filters.country || filters.use_case || filters.excluded !== '' || filters.date_from || filters.date_to || filters.search

    return (
        <AdminLayout title="Request Log">
            {/* Filter bar */}
            <div className="card shadow-sm mb-3">
                <div className="card-body py-2">
                    <div className="row g-2 align-items-end">
                        {/* Search */}
                        <div className="col-sm-4">
                            <input
                                type="text"
                                className="form-control form-control-sm"
                                placeholder="Device token, name or phone…"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && applyFilters({ search })}
                                onBlur={() => search !== filters.search && applyFilters({ search })}
                            />
                        </div>

                        {/* Country */}
                        <div className="col-sm-2">
                            <input
                                type="text"
                                className="form-control form-control-sm"
                                placeholder="Country code"
                                maxLength={2}
                                value={filters.country}
                                onChange={(e) => applyFilters({ country: e.target.value.toUpperCase() })}
                            />
                        </div>

                        {/* Use case */}
                        <div className="col-sm-2">
                            <select
                                className="form-select form-select-sm"
                                value={filters.use_case}
                                onChange={(e) => applyFilters({ use_case: e.target.value })}
                            >
                                <option value="">All use cases</option>
                                {use_cases.map((uc) => (
                                    <option key={uc} value={uc}>{uc}</option>
                                ))}
                            </select>
                        </div>

                        {/* Excluded */}
                        <div className="col-sm-2">
                            <select
                                className="form-select form-select-sm"
                                value={filters.excluded}
                                onChange={(e) => applyFilters({ excluded: e.target.value })}
                            >
                                <option value="">All requests</option>
                                <option value="0">Not excluded</option>
                                <option value="1">Excluded only</option>
                            </select>
                        </div>

                        {/* Date range */}
                        <div className="col-sm-auto">
                            <input
                                type="date"
                                className="form-control form-control-sm"
                                value={filters.date_from}
                                onChange={(e) => applyFilters({ date_from: e.target.value })}
                            />
                        </div>
                        <div className="col-sm-auto">
                            <input
                                type="date"
                                className="form-control form-control-sm"
                                value={filters.date_to}
                                onChange={(e) => applyFilters({ date_to: e.target.value })}
                            />
                        </div>

                        {hasFilters && (
                            <div className="col-auto">
                                <button
                                    className="btn btn-outline-secondary btn-sm"
                                    onClick={() => { setSearch(''); applyFilters({ country: '', use_case: '', excluded: '', date_from: '', date_to: '', search: '' }) }}
                                >
                                    Clear
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <div className="d-flex align-items-center mb-2">
                <span className="text-muted small">{requests.meta.total.toLocaleString()} requests</span>
            </div>

            <DataTable
                columns={columns}
                data={requests.data as unknown as Record<string, unknown>[]}
                pagination={requests.meta}
                links={requests.links}
                sortBy={filters.sort_by}
                sortDir={filters.sort_dir}
                onSort={handleSort}
                onPageChange={handlePageChange}
                actions={(row) => (
                    <Link href={`/admin/requests/${row.id}`} className="btn btn-outline-secondary btn-sm">
                        View
                    </Link>
                )}
            />
        </AdminLayout>
    )
}
