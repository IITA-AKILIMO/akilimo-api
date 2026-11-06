import type { ReactNode } from 'react'
import type { PaginationLinks, PaginationMeta } from '@/types'
import Pagination from './Pagination'

export interface Column {
    key: string
    label: string
    sortable?: boolean
    render?: (value: any, row: Record<string, unknown>) => ReactNode
}

interface DataTableProps {
    columns: Column[]
    data: Record<string, any>[]
    pagination: PaginationMeta
    links: PaginationLinks
    sortBy?: string
    sortDir?: 'asc' | 'desc'
    onSort: (col: string) => void
    onPageChange: (page: number) => void
    actions?: (row: Record<string, any>) => ReactNode
}

function SortIcon({ active, dir }: { active: boolean; dir: 'asc' | 'desc' }) {
    return (
        <span className={`sort-icon${active ? ' active' : ''}`}>
            <svg className={active && dir === 'asc' ? 'lit' : ''} width="10" height="6" viewBox="0 0 10 6" fill="currentColor">
                <path d="M5 0L9.33 6H.67L5 0z" />
            </svg>
            <svg className={active && dir === 'desc' ? 'lit' : ''} width="10" height="6" viewBox="0 0 10 6" fill="currentColor">
                <path d="M5 6L.67 0h8.66L5 6z" />
            </svg>
        </span>
    )
}

export default function DataTable({
    columns,
    data,
    pagination,
    links,
    sortBy,
    sortDir = 'asc',
    onSort,
    onPageChange,
    actions,
}: DataTableProps) {
    return (
        <div className="card shadow-sm">
            <div className="table-responsive">
                <table className="table table-hover table-striped align-middle mb-0">
                    <thead className="table-light">
                        <tr>
                            {columns.map((col) => (
                                <th
                                    key={col.key}
                                    scope="col"
                                    className={`fw-semibold text-uppercase small${col.sortable ? ' user-select-none' : ''}`}
                                    style={col.sortable ? { cursor: 'pointer' } : undefined}
                                    onClick={col.sortable ? () => onSort(col.key) : undefined}
                                >
                                    {col.label}
                                    {col.sortable && (
                                        <SortIcon
                                            active={sortBy === col.key}
                                            dir={sortBy === col.key ? sortDir : 'asc'}
                                        />
                                    )}
                                </th>
                            ))}
                            {actions && (
                                <th scope="col" className="text-end fw-semibold text-uppercase small">
                                    Actions
                                </th>
                            )}
                        </tr>
                    </thead>
                    <tbody>
                        {data.length === 0 ? (
                            <tr>
                                <td
                                    colSpan={columns.length + (actions ? 1 : 0)}
                                    className="text-center text-muted py-5"
                                >
                                    <svg className="mb-2 d-block mx-auto text-secondary" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    No records found
                                </td>
                            </tr>
                        ) : (
                            data.map((row, rowIdx) => (
                                <tr key={rowIdx}>
                                    {columns.map((col) => (
                                        <td key={col.key}>
                                            {col.render
                                                ? col.render(row[col.key], row)
                                                : String(row[col.key] ?? '—')}
                                        </td>
                                    ))}
                                    {actions && (
                                        <td className="text-end">{actions(row)}</td>
                                    )}
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {pagination.last_page > 1 && (
                <div className="card-footer bg-white">
                    <Pagination meta={pagination} links={links} onPageChange={onPageChange} />
                </div>
            )}
        </div>
    )
}
