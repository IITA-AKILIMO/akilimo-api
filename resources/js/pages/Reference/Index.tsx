import { Link, router } from '@inertiajs/react'
import { useState } from 'react'
import ConfirmDialog from '../../components/ConfirmDialog'
import { PreviewPanel, requestPreview } from '../../components/PricePreview'
import type { PreviewOutcome } from '../../components/PricePreview'
import AdminLayout from '../../layouts/AdminLayout'
import type { ReferenceResource, ReferenceRow } from '../../types'

interface Props {
    resource: ReferenceResource
    rows: ReferenceRow[]
}

/** ?key[column]=value for the row's key columns, as the server reads it */
export function keyQuery(key: Record<string, unknown>): string {
    return Object.entries(key).map(([k, v]) => `key[${encodeURIComponent(k)}]=${encodeURIComponent(String(v))}`).join('&')
}

export default function ReferenceIndex({ resource, rows }: Props) {
    const [deleting, setDeleting] = useState<ReferenceRow | null>(null)
    const [processing, setProcessing] = useState(false)
    // For a table that drives the app's price lists: what removing the row would do to them
    const [outcome, setOutcome] = useState<PreviewOutcome | null>(null)
    const [loadingPreview, setLoadingPreview] = useState(false)
    const base = `/admin/reference/${resource.slug}`

    async function startDelete(row: ReferenceRow) {
        setDeleting(row)
        setOutcome(null)

        if (!resource.canPreview) return

        setLoadingPreview(true)
        setOutcome(await requestPreview(`${base}/preview?delete=1&${keyQuery(row._key)}`))
        setLoadingPreview(false)
    }

    function cancelDelete() {
        setDeleting(null)
        setOutcome(null)
        setLoadingPreview(false)
    }

    function handleDelete() {
        if (!deleting) return
        setProcessing(true)
        router.delete(`${base}?${keyQuery(deleting._key)}`, {
            onFinish: () => { setProcessing(false); cancelDelete() },
        })
    }

    const name = deleting ? Object.values(deleting._key).join(' / ') : ''
    const blocked = outcome?.kind === 'blocked'

    return (
        <AdminLayout title={resource.title}>
            <div className="d-flex align-items-start justify-content-between mb-3 gap-3">
                <div style={{ maxWidth: 760 }}>
                    <Link href="/admin/reference" className="small">&larr; Price settings</Link>
                    <p className="text-body-secondary mt-1 mb-1">{resource.description}</p>
                    {resource.warning && <p className="small text-warning-emphasis mb-0">{resource.warning}</p>}
                </div>
                <Link href={resource.createUrl ?? `${base}/create`} className="btn btn-success btn-sm text-nowrap">+ New</Link>
            </div>

            <div className="card border-0 shadow-sm">
                <div className="table-responsive">
                    <table className="table table-hover align-middle mb-0">
                        <thead className="table-light">
                            <tr>
                                {resource.fields.map((f) => <th key={f.name}>{f.label}</th>)}
                                <th style={{ width: 150 }}></th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows.length === 0 && (
                                <tr><td colSpan={resource.fields.length + 1} className="text-center text-body-secondary py-4">Nothing here yet.</td></tr>
                            )}
                            {rows.map((row) => (
                                <tr key={keyQuery(row._key)}>
                                    {resource.fields.map((f) => <td key={f.name}>{row[f.name] === null || row[f.name] === undefined ? '' : String(row[f.name])}</td>)}
                                    <td className="text-end">
                                        <div className="d-flex gap-1 justify-content-end">
                                            <Link href={`${base}/edit?${keyQuery(row._key)}`} className="btn btn-outline-secondary btn-sm">Edit</Link>
                                            <button className="btn btn-outline-danger btn-sm" onClick={() => startDelete(row)}>Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <ConfirmDialog
                open={!!deleting}
                wide={resource.canPreview}
                title={`Delete from ${resource.title.toLowerCase()}`}
                message={
                    <>
                        <div>Delete &ldquo;{name}&rdquo;? This cannot be undone. A row that other data still uses cannot be deleted.</div>
                        {resource.canPreview && loadingPreview && (
                            <div className="small mt-3"><span className="spinner-border spinner-border-sm me-2" />Working out what this would change…</div>
                        )}
                        {outcome?.kind === 'ok' && <PreviewPanel result={outcome.result} verb="remove or change" />}
                        {outcome && outcome.kind !== 'ok' && (
                            <div className={`alert ${blocked ? 'alert-danger' : 'alert-warning'} small mt-3 mb-0`} role="alert">{outcome.message}</div>
                        )}
                    </>
                }
                confirmDisabled={loadingPreview || blocked}
                onConfirm={handleDelete}
                onCancel={cancelDelete}
                processing={processing}
            />
        </AdminLayout>
    )
}
