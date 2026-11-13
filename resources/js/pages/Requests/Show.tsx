import { Link, router, usePage } from '@inertiajs/react'
import { useState } from 'react'
import AdminLayout from '../../layouts/AdminLayout'
import type { ApiRequestDetail, PageProps } from '../../types'

interface Props {
    request: ApiRequestDetail & {
        full_names: string | null
        phone_number: string | null
        gender_code: string | null
        // The PDF report kept for this request, with fresh signed links, or null
        report: { expires_at: string; files: { name: string; url: string }[] } | null
    }
}

function Field({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="col-sm-6 col-md-4">
            <p className="text-muted small mb-0">{label}</p>
            <p className="fw-medium mb-2">{value ?? '—'}</p>
        </div>
    )
}

function CopyButton({ text }: { text: string }) {
    const [copied, setCopied] = useState(false)

    function handleCopy() {
        navigator.clipboard.writeText(text).then(() => {
            setCopied(true)
            setTimeout(() => setCopied(false), 2000)
        })
    }

    return (
        <button
            type="button"
            onClick={handleCopy}
            className="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
            title="Copy to clipboard"
        >
            {copied ? (
                <>
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Copied
                </>
            ) : (
                <>
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copy
                </>
            )}
        </button>
    )
}

interface JsonPanelProps {
    title: string
    subtitle?: string
    data: Record<string, unknown>
    defaultOpen?: boolean
}

function JsonPanel({ title, subtitle, data, defaultOpen = true }: JsonPanelProps) {
    const [open, setOpen] = useState(defaultOpen)
    const json = JSON.stringify(data, null, 2)

    return (
        <div className="card shadow-sm">
            <div className="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                <button
                    type="button"
                    className="json-panel-toggle d-flex align-items-center gap-2 flex-grow-1"
                    onClick={() => setOpen((v) => !v)}
                    aria-expanded={open}
                >
                    <svg
                        width="12" height="12" viewBox="0 0 12 12" fill="currentColor"
                        style={{
                            opacity: 0.5,
                            transition: 'transform 0.2s',
                            transform: open ? 'rotate(180deg)' : 'rotate(0deg)',
                            flexShrink: 0,
                        }}
                    >
                        <path d="M6 8L1 3h10L6 8z" />
                    </svg>
                    <h6 className="mb-0 fw-semibold">
                        {title}
                        {subtitle && <span className="text-muted fw-normal small ms-1">{subtitle}</span>}
                    </h6>
                </button>
                <CopyButton text={json} />
            </div>
            <div style={{ overflow: 'hidden', maxHeight: open ? '600px' : '0', transition: 'max-height 0.25s ease' }}>
                <div className="card-body p-0">
                    <pre
                        className="bg-dark text-success rounded-bottom p-3 small mb-0"
                        style={{ maxHeight: 400, overflowY: 'auto', whiteSpace: 'pre-wrap', wordBreak: 'break-all' }}
                    >
                        {json}
                    </pre>
                </div>
            </div>
        </div>
    )
}

export default function RequestShow({ request }: Props) {
    const { auth } = usePage<PageProps>().props
    const isAdmin = auth.user?.role === 'admin'
    const [regenerating, setRegenerating] = useState(false)
    return (
        <AdminLayout title="Request Detail">
            <div className="mb-3">
                <Link href="/admin/requests" className="btn btn-outline-secondary btn-sm">
                    ← Back to Request Log
                </Link>
            </div>

            {/* Summary card */}
            <div className="card shadow-sm mb-4">
                <div className="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 className="mb-0 fw-semibold">Request #{request.id}</h6>
                    <div className="d-flex gap-2">
                        {request.excluded && <span className="badge bg-danger">Excluded</span>}
                        {request.use_case && (
                            <span className="badge bg-primary">{request.use_case}</span>
                        )}
                    </div>
                </div>
                <div className="card-body">
                    <div className="row">
                        <Field label="Request ID" value={<code className="small">{request.request_id}</code>} />
                        <Field label="Device Token" value={request.device_token ? <code className="small">{request.device_token}</code> : null} />
                        <Field label="Country" value={request.country_code
                            ? <span className="badge bg-light text-dark border">{request.country_code}</span>
                            : null}
                        />
                        <Field label="Full Names" value={request.full_names} />
                        <Field label="Phone Number" value={request.phone_number} />
                        <Field label="Gender" value={request.gender_code} />
                        <Field label="Duration" value={request.duration_ms != null ? `${request.duration_ms}ms` : null} />
                        <Field label="Date" value={request.created_at ? new Date(request.created_at).toLocaleString() : null} />
                        <Field label="PDF report" value={
                            <>
                                {request.report?.files.map((f) => (
                                    <a key={f.name} href={f.url} className="btn btn-outline-success btn-sm me-2 mb-1" target="_blank" rel="noopener noreferrer">
                                        Download {f.name}
                                    </a>
                                ))}
                                {!request.report && <span className="text-muted me-2">None kept</span>}
                                {isAdmin && (
                                    <button type="button" className="btn btn-outline-secondary btn-sm mb-1" disabled={regenerating}
                                        title="Make the report again from the request that was sent, with the current report template"
                                        onClick={() => router.post(`/admin/requests/${request.id}/report`, {}, {
                                            preserveScroll: true,
                                            onStart: () => setRegenerating(true),
                                            onFinish: () => setRegenerating(false),
                                        })}>
                                        {regenerating ? 'Making the report…' : request.report ? 'Make it again' : 'Make the report'}
                                    </button>
                                )}
                            </>
                        }
                        />
                    </div>
                </div>
            </div>

            {/* JSON panels */}
            <div className="row g-4">
                <div className="col-lg-6">
                    <JsonPanel
                        title="Client Request"
                        subtitle="(droid)"
                        data={request.droid_request}
                    />
                </div>
                <div className="col-lg-6">
                    <JsonPanel
                        title="Compute Request"
                        subtitle="(plumber)"
                        data={request.plumber_request}
                    />
                </div>
                <div className="col-12">
                    <JsonPanel
                        title="Compute Response"
                        subtitle="(plumber)"
                        data={request.plumber_response}
                        defaultOpen={false}
                    />
                </div>
            </div>
        </AdminLayout>
    )
}
