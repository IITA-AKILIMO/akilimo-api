import { router } from '@inertiajs/react'
import { useRef, useState } from 'react'
import AdminLayout from '../../layouts/AdminLayout'

interface Props {
    apiKey: {
        id: number
        name: string
        key_prefix: string
        abilities: string[] | null
        expires_at: string | null
        user: { name: string; email: string } | null
    }
    plainTextKey: string
}

export default function ApiKeysCreated({ apiKey, plainTextKey }: Props) {
    const [visible, setVisible] = useState(false)
    const [copied, setCopied] = useState(false)
    const inputRef = useRef<HTMLInputElement>(null)

    function markCopied() {
        setCopied(true)
        setTimeout(() => setCopied(false), 2000)
    }

    function copy() {
        // navigator.clipboard needs a secure context; fall back to execCommand on plain http hosts
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(plainTextKey).then(markCopied)
            return
        }
        const input = inputRef.current
        if (!input) return
        const wasHidden = input.type === 'password'
        input.type = 'text'
        input.select()
        document.execCommand('copy')
        input.setSelectionRange(0, 0)
        if (wasHidden) input.type = 'password'
        markCopied()
    }

    function done() {
        // Replace this history entry so Back doesn't return to the page holding the key
        router.visit('/admin/api-keys', { replace: true })
    }

    return (
        <AdminLayout title="API Key Created">
            <div className="mx-auto" style={{ maxWidth: 640 }}>
                <div className="card">
                    <div className="card-body">
                        <h5 className="card-title mb-3">API key "{apiKey.name}" created</h5>

                        <div className="alert alert-warning small" role="alert">
                            <strong>Copy this key now — it will not be shown again.</strong>{' '}
                            Only a hash is stored. If you lose it, revoke this key and create a new one.
                        </div>

                        <label className="form-label small text-muted" htmlFor="plain-text-key">API key</label>
                        <div className="input-group mb-3">
                            <input
                                id="plain-text-key"
                                ref={inputRef}
                                type={visible ? 'text' : 'password'}
                                className="form-control font-monospace"
                                value={plainTextKey}
                                readOnly
                                autoComplete="off"
                                spellCheck={false}
                                onFocus={(e) => e.target.select()}
                            />
                            <button
                                type="button"
                                className="btn btn-outline-secondary"
                                onClick={() => setVisible((v) => !v)}
                            >
                                {visible ? 'Hide' : 'Show'}
                            </button>
                            <button
                                type="button"
                                className={`btn ${copied ? 'btn-success' : 'btn-outline-primary'}`}
                                onClick={copy}
                            >
                                {copied ? 'Copied' : 'Copy'}
                            </button>
                        </div>

                        <dl className="row small mb-3">
                            <dt className="col-sm-3 text-muted fw-normal">Prefix</dt>
                            <dd className="col-sm-9"><code>{apiKey.key_prefix}</code></dd>
                            <dt className="col-sm-3 text-muted fw-normal">User</dt>
                            <dd className="col-sm-9">{apiKey.user ? `${apiKey.user.name} (${apiKey.user.email})` : '—'}</dd>
                            <dt className="col-sm-3 text-muted fw-normal">Abilities</dt>
                            <dd className="col-sm-9">
                                {apiKey.abilities && apiKey.abilities.length > 0 ? apiKey.abilities.join(', ') : <code>*</code>}
                            </dd>
                            <dt className="col-sm-3 text-muted fw-normal">Expires</dt>
                            <dd className="col-sm-9">
                                {apiKey.expires_at ? new Date(apiKey.expires_at).toLocaleDateString() : 'Never'}
                            </dd>
                        </dl>

                        <p className="small text-muted mb-3">
                            Send it as <code>X-Api-Key: &lt;key&gt;</code> or <code>Authorization: Bearer &lt;key&gt;</code>.
                        </p>

                        <div className="d-flex justify-content-end">
                            <button type="button" className="btn btn-success" onClick={done}>
                                I've saved it — back to API keys
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    )
}
