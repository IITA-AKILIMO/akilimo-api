import { router } from '@inertiajs/react'
import { useState } from 'react'
import DataTable from '../../components/DataTable'
import type { Column } from '../../components/DataTable'
import AdminLayout from '../../layouts/AdminLayout'
import type { Paginated, UserFeedback } from '../../types'
import DateInput from '../../components/DateInput'

interface Props {
    feedback: Paginated<UserFeedback>
    filters: {
        use_case: string
        user_type: string
        language: string
        date_from: string
        date_to: string
        search: string
        sort_by: string
        sort_dir: 'asc' | 'desc'
    }
    use_cases: string[]
    languages: string[]
}

function RatingStars({ value, max = 5 }: { value: number; max?: number }) {
    return (
        <span title={`${value}/${max}`}>
            {Array.from({ length: max }).map((_, i) => (
                <span key={i} style={{ color: i < value ? '#f59e0b' : '#d1d5db' }}>★</span>
            ))}
        </span>
    )
}

function navigate(params: Record<string, unknown>) {
    // @ts-ignore
    router.get('/admin/feedback', params, { preserveScroll: true, replace: true })
}

export default function FeedbackIndex({ feedback, filters, use_cases, languages }: Props) {
    const [search, setSearch] = useState(filters.search)

    const columns: Column[] = [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'device_token', label: 'Device', sortable: false, render: (v) => v ? <code className="small">{v as string}</code> : '—' },
        { key: 'use_case', label: 'Use Case', sortable: false, render: (v) => v ? <span className="badge bg-secondary">{v as string}</span> : '—' },
        { key: 'user_type', label: 'User Type', sortable: true, render: (v) => v ?? '—' },
        {
            key: 'akilimo_rec_rating', label: 'Rec Rating', sortable: true,
            render: (v) => <RatingStars value={v as number} />,
        },
        {
            key: 'akilimo_useful_rating', label: 'Useful Rating', sortable: true,
            render: (v) => <RatingStars value={v as number} />,
        },
        { key: 'language', label: 'Lang', sortable: true, render: (v) => v ?? '—' },
        {
            key: 'created_at', label: 'Date', sortable: true,
            render: (v) => v ? new Date(v as string).toLocaleString() : '—',
        },
    ]

    function applyFilters(overrides: Partial<typeof filters>) {
        navigate({ ...filters, ...overrides, page: 1 })
    }

    function handleSort(col: string) {
        const dir = filters.sort_by === col && filters.sort_dir === 'asc' ? 'desc' : 'asc'
        navigate({ ...filters, sort_by: col, sort_dir: dir, page: 1 })
    }

    function handlePageChange(page: number) {
        navigate({ ...filters, page })
    }

    const hasFilters = filters.use_case || filters.user_type || filters.language || filters.date_from || filters.date_to || filters.search

    return (
        <AdminLayout title="User Feedback">
            {/* Filter bar */}
            <div className="card shadow-sm mb-3">
                <div className="card-body py-2">
                    <div className="row g-2 align-items-end">
                        <div className="col-sm-3">
                            <input
                                type="text"
                                className="form-control form-control-sm"
                                placeholder="Search device token…"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && applyFilters({ search })}
                                onBlur={() => search !== filters.search && applyFilters({ search })}
                            />
                        </div>

                        <div className="col-sm-2">
                            <select
                                className="form-select form-select-sm"
                                value={filters.use_case}
                                onChange={(e) => applyFilters({ use_case: e.target.value })}
                            >
                                <option value="">All use cases</option>
                                {use_cases.map((uc) => <option key={uc} value={uc}>{uc}</option>)}
                            </select>
                        </div>

                        <div className="col-sm-2">
                            <select
                                className="form-select form-select-sm"
                                value={filters.language}
                                onChange={(e) => applyFilters({ language: e.target.value })}
                            >
                                <option value="">All languages</option>
                                {languages.map((l) => <option key={l} value={l}>{l}</option>)}
                            </select>
                        </div>

                        <div className="col-sm-auto">
                            <DateInput small ariaLabel="From date" value={filters.date_from} max={filters.date_to || undefined}
                                onChange={(v) => applyFilters({ date_from: v })} />
                        </div>
                        <div className="col-sm-auto">
                            <DateInput small ariaLabel="To date" value={filters.date_to} min={filters.date_from || undefined}
                                onChange={(v) => applyFilters({ date_to: v })} />
                        </div>

                        {hasFilters && (
                            <div className="col-auto">
                                <button className="btn btn-outline-secondary btn-sm"
                                    onClick={() => { setSearch(''); applyFilters({ use_case: '', user_type: '', language: '', date_from: '', date_to: '', search: '' }) }}>
                                    Clear
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <div className="d-flex align-items-center mb-2">
                <span className="text-muted small">{feedback.meta.total.toLocaleString()} responses</span>
            </div>

            <DataTable
                columns={columns}
                data={feedback.data as unknown as Record<string, unknown>[]}
                pagination={feedback.meta}
                links={feedback.links}
                sortBy={filters.sort_by}
                sortDir={filters.sort_dir}
                onSort={handleSort}
                onPageChange={handlePageChange}
            />
        </AdminLayout>
    )
}
