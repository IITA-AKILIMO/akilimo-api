import {useEffect, useState} from 'react'
import {createKey, deleteKey, fetchKeys, revokeKey} from '../api'
import Icon from '../components/Icon'
import Spinner from '../components/Spinner'
import {timeAgo} from '../lib/format'
import type {ApiKey, KeyType, NewKey} from '../types'
import KeyRevealBanner, {MCP_URL, isMcpKey} from './KeyRevealBanner'

export default function TokensPanel() {
    const [keys, setKeys] = useState<ApiKey[]>([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)
    const [newKey, setNewKey] = useState<NewKey | null>(null)
    const [creating, setCreating] = useState(false)
    const [createError, setCreateError] = useState<string | null>(null)
    const [name, setName] = useState('')
    const [type, setType] = useState<KeyType>('api')
    const [pendingAction, setPendingAction] = useState<number | null>(null)

    function load() {
        setLoading(true)
        setError(null)
        fetchKeys()
            .then((d) => setKeys(d))
            .catch(() => setError('Could not load API keys. Please try again.'))
            .finally(() => setLoading(false))
    }

    useEffect(() => { load() }, [])

    async function handleCreate(e: React.FormEvent) {
        e.preventDefault()
        if (!name.trim()) return

        setCreating(true)
        setCreateError(null)

        const outcome = await createKey(name.trim(), type)
        if (outcome.ok) {
            setNewKey(outcome.key)
            setName('')
            setKeys((prev) => [outcome.key, ...prev])
        } else {
            setCreateError(outcome.message)
        }
        setCreating(false)
    }

    async function handleRevoke(id: number) {
        setPendingAction(id)
        try {
            if (await revokeKey(id)) {
                setKeys((prev) => prev.map((k) => k.id === id ? {...k, is_active: false} : k))
            }
        } finally {
            setPendingAction(null)
        }
    }

    async function handleDelete(id: number) {
        if (!confirm('Delete this API key? This cannot be undone.')) return

        setPendingAction(id)
        try {
            if (await deleteKey(id)) {
                setKeys((prev) => prev.filter((k) => k.id !== id))
                if (newKey?.id === id) setNewKey(null)
            }
        } finally {
            setPendingAction(null)
        }
    }

    return (
        <div className="d-grid gap-3">
            <div className="card">
                <div className="card-body p-4">
                    <h2 className="h5 fw-bold">Generate API key</h2>
                    <p className="text-body-secondary small">
                        An <strong>API key</strong> calls this API; send it in the <code>X-Api-Key</code> header.
                        An <strong>MCP key</strong> connects your own MCP client (Claude Desktop, Open WebUI) to the
                        AKILIMO tools at <code>{MCP_URL}</code> and works only there.
                    </p>

                    <form className="row g-2" onSubmit={handleCreate}>
                        <div className="col-12 col-sm-3">
                            <select className="form-select" value={type} aria-label="Key type"
                                    onChange={(e) => setType(e.target.value as KeyType)}>
                                <option value="api">API key</option>
                                <option value="mcp">MCP key</option>
                            </select>
                        </div>
                        <div className="col-12 col-sm">
                            <input type="text" className="form-control" placeholder="Key name, e.g. My App"
                                   aria-label="Key name" value={name} maxLength={100} required
                                   onChange={(e) => setName(e.target.value)}/>
                        </div>
                        <div className="col-12 col-sm-auto">
                            <button type="submit" className="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2"
                                    disabled={creating || !name.trim()}>
                                {creating ? <Spinner/> : <Icon name="plus"/>}
                                Generate
                            </button>
                        </div>
                    </form>

                    {createError && <div className="alert alert-danger py-2 small mt-3 mb-0" role="alert">{createError}</div>}
                </div>
            </div>

            {newKey && <KeyRevealBanner apiKey={newKey} onDismiss={() => setNewKey(null)}/>}

            <div className="card">
                <div className="card-body p-4">
                    <div className="d-flex justify-content-between align-items-center mb-3">
                        <h2 className="h5 fw-bold mb-0">Your API keys</h2>
                        <button type="button" className="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" onClick={load}>
                            <Icon name="refresh" size={13}/> Refresh
                        </button>
                    </div>

                    {loading && <div className="text-center text-body-secondary py-4"><Spinner text="Loading keys…"/></div>}

                    {error && (
                        <div className="alert alert-danger d-flex justify-content-between align-items-center mb-0" role="alert">
                            <span>{error}</span>
                            <button type="button" className="btn btn-sm btn-outline-danger" onClick={load}>Retry</button>
                        </div>
                    )}

                    {!loading && !error && keys.length === 0 && (
                        <div className="text-center py-4">
                            <p className="mb-1">No API keys yet.</p>
                            <p className="small text-body-secondary mb-0">Use the form above to generate your first key.</p>
                        </div>
                    )}

                    {!loading && keys.length > 0 && (
                        <ul className="list-group list-group-flush">
                            {keys.map((k) => (
                                <li key={k.id} className={`list-group-item px-0 ${k.is_active ? '' : 'text-body-secondary'}`}>
                                    <div className="d-flex flex-wrap align-items-center gap-2">
                                        <span className="fw-semibold">{k.name}</span>
                                        {isMcpKey(k) && <span className="badge text-bg-info">MCP</span>}
                                        <code className="small">{k.key_prefix}…</code>
                                        <span className={`badge ${k.is_active ? 'text-bg-success' : 'text-bg-secondary'}`}>
                                            {k.is_active ? 'Active' : 'Revoked'}
                                        </span>
                                        <span className="ms-auto d-inline-flex gap-2">
                                            {k.is_active && (
                                                <button type="button" className="btn btn-sm btn-outline-warning" title="Revoke key"
                                                        onClick={() => handleRevoke(k.id)} disabled={pendingAction === k.id}>
                                                    {pendingAction === k.id ? '…' : 'Revoke'}
                                                </button>
                                            )}
                                            <button type="button" className="btn btn-sm btn-outline-danger" title="Delete key"
                                                    onClick={() => handleDelete(k.id)} disabled={pendingAction === k.id}>
                                                {pendingAction === k.id ? '…' : 'Delete'}
                                            </button>
                                        </span>
                                    </div>
                                    <div className="small text-body-secondary mt-1 d-flex flex-wrap column-gap-3">
                                        {k.last_used_at && <span>Used {timeAgo(k.last_used_at)}</span>}
                                        {k.created_at && <span>Created {timeAgo(k.created_at)}</span>}
                                        <span className="font-monospace">{(k.abilities ?? ['*']).join(', ')}</span>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </div>
    )
}
