import { useForm } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'
import type { PreviewResult, ReferenceField, ReferenceResource } from '../../types'
import { keyQuery } from './Index'

interface Props {
    resource: ReferenceResource
    /** The row being edited, or null when adding */
    row: Record<string, string | number | null> | null
    rowKey?: Record<string, string>
    options: Record<string, { value: string; label: string }[]>
}

function initial(resource: ReferenceResource, row: Props['row']): Record<string, string> {
    const values: Record<string, string> = {}
    for (const f of resource.fields) {
        const current = row ? row[f.name] : f.default
        values[f.name] = current === null || current === undefined ? '' : String(current)
    }
    return values
}

/** Laravel's CSRF token for a plain fetch: the XSRF-TOKEN cookie, sent back as a header */
function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/)
    return match ? decodeURIComponent(match[1]) : ''
}

export default function ReferenceForm({ resource, row, rowKey, options }: Props) {
    const editing = row !== null
    const base = `/admin/reference/${resource.slug}`
    const { data, setData, post, put, processing, errors } = useForm<Record<string, string>>(initial(resource, row))

    const [preview, setPreview] = useState<PreviewResult | null>(null)
    const [previewing, setPreviewing] = useState(false)
    const [previewError, setPreviewError] = useState<string | null>(null)

    // A preview describes the values as they were when it was made; any edit makes it stale
    useEffect(() => { setPreview(null); setPreviewError(null) }, [JSON.stringify(data)])

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        if (editing) put(`${base}?${keyQuery(rowKey ?? {})}`)
        else post(base)
    }

    async function runPreview() {
        setPreviewing(true)
        setPreview(null)
        setPreviewError(null)
        try {
            const response = await fetch(`${base}/preview${editing ? `?${keyQuery(rowKey ?? {})}` : ''}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': xsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(data),
            })
            const body = await response.json()

            if (response.status === 422) {
                const first = Object.values((body.errors ?? {}) as Record<string, string[]>)[0]?.[0]
                setPreviewError(first ?? 'Some values are not valid yet.')
            } else if (!response.ok) {
                setPreviewError('The preview could not be worked out.')
            } else {
                setPreview(body as PreviewResult)
            }
        } catch {
            setPreviewError('The preview could not be worked out.')
        } finally {
            setPreviewing(false)
        }
    }

    function control(f: ReferenceField) {
        // A row's key is read-only once it exists: others may point to it
        const readOnly = editing && f.key
        const invalid = errors[f.name] ? 'is-invalid' : ''

        if (f.type === 'select') {
            const list = options[f.name] ?? []
            return (
                <select className={`form-select ${invalid}`} value={data[f.name]} disabled={readOnly} onChange={(e) => setData(f.name, e.target.value)}>
                    <option value="">Select…</option>
                    {data[f.name] && !list.some((o) => o.value === data[f.name]) && <option value={data[f.name]}>{data[f.name]} (not in list)</option>}
                    {list.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                </select>
            )
        }

        return (
            <input
                type={f.type === 'number' ? 'number' : 'text'}
                step={f.type === 'number' ? (f.step ?? '1') : undefined}
                className={`form-control ${invalid}`}
                value={data[f.name]}
                readOnly={readOnly}
                onChange={(e) => setData(f.name, e.target.value)}
            />
        )
    }

    return (
        <AdminLayout title={editing ? `Edit ${resource.title}` : `New: ${resource.title}`}>
            <div className="mx-auto" style={{ maxWidth: resource.canPreview ? 820 : 640 }}>
                <ResourceForm title={editing ? `Edit ${resource.title.toLowerCase()}` : `Add to ${resource.title.toLowerCase()}`} onSubmit={handleSubmit} processing={processing} onCancel={() => window.history.back()}>
                    {resource.warning && <p className="small text-warning-emphasis">{resource.warning}</p>}
                    <div className="row g-3">
                        {resource.fields.map((f) => (
                            <div className="col-12" key={f.name}>
                                <FormField label={f.label} required={f.required && !(editing && f.key)} error={errors[f.name]}>
                                    {control(f)}
                                    {f.help && <div className="form-text">{f.help}</div>}
                                </FormField>
                            </div>
                        ))}
                    </div>

                    {resource.canPreview && (
                        <div className="mt-4 pt-3 border-top">
                            <div className="d-flex align-items-center gap-3 flex-wrap">
                                <button type="button" className="btn btn-outline-primary btn-sm" onClick={runPreview} disabled={previewing}>
                                    {previewing ? <><span className="spinner-border spinner-border-sm me-2" />Working it out…</> : 'Preview the change'}
                                </button>
                                <span className="small text-body-secondary">Shows what this would do to the app&apos;s price lists. Nothing is saved.</span>
                            </div>

                            {previewError && <div className="alert alert-warning small mt-3 mb-0" role="alert">{previewError}</div>}

                            {preview && <PreviewPanel result={preview} />}
                        </div>
                    )}
                </ResourceForm>
            </div>
        </AdminLayout>
    )
}

function PreviewPanel({ result }: { result: PreviewResult }) {
    if (result.changed === 0) {
        return <div className="alert alert-secondary small mt-3 mb-0" role="status">No value in any price list would change.</div>
    }

    return (
        <div className="mt-3" role="status" aria-live="polite">
            <p className="fw-semibold mb-2">
                This would change {result.changed} value{result.changed === 1 ? '' : 's'} in the app&apos;s price lists.
            </p>
            {result.lists.filter((l) => l.changed + l.added + l.removed > 0).map((list) => (
                <div key={list.key} className="mb-3">
                    <div className="d-flex align-items-baseline justify-content-between">
                        <h6 className="mb-1">{list.title}</h6>
                        <span className="small text-body-secondary">
                            {[
                                list.changed ? `${list.changed} changed` : '',
                                list.added ? `${list.added} new` : '',
                                list.removed ? `${list.removed} removed` : '',
                            ].filter(Boolean).join(', ')} of {list.total}
                        </span>
                    </div>
                    <div className="table-responsive">
                        <table className="table table-sm align-middle mb-0">
                            <thead className="table-light">
                                <tr><th>Row</th><th className="text-end">Now</th><th className="text-end">After</th></tr>
                            </thead>
                            <tbody>
                                {list.rows.map((r, i) => (
                                    <tr key={i}>
                                        <td>{r.label}</td>
                                        <td className="text-end text-body-secondary">{r.before ?? '—'}</td>
                                        <td className="text-end fw-semibold">{r.after ?? 'removed'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    {list.truncated && <div className="small text-body-secondary mt-1">Showing the first {list.rows.length} changes.</div>}
                </div>
            ))}
        </div>
    )
}
