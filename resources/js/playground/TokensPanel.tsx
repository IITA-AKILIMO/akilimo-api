import { useEffect, useRef, useState } from 'react'

interface ApiKey {
    id: number
    name: string
    key_prefix: string
    is_active: boolean
    abilities: string[]
    last_used_at: string | null
    expires_at: string | null
    created_at: string | null
}

interface NewKey extends ApiKey {
    key: string
}

type KeyType = 'api' | 'mcp'

// The AKILIMO MCP server (akilimo-mcp-agent) that MCP keys are for
const MCP_URL = 'https://akilimo-mcp.akilimo.org/mcp'
const MCP_USE = 'mcp:use'

const isMcpKey = (k: ApiKey) => (k.abilities ?? []).length === 1 && k.abilities[0] === MCP_USE

function mcpConfig(key: string): string {
    return JSON.stringify({
        mcpServers: { 'akilimo-1.0': { type: 'http', url: MCP_URL, headers: { Authorization: `Bearer ${key}` } } },
    }, null, 2)
}

function timeAgo(iso: string): string {
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000)
    if (diff < 60) return `${diff}s ago`
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
    return `${Math.floor(diff / 86400)}d ago`
}

function csrfToken(): string {
    return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? ''
}

function KeyRevealBanner({ apiKey, onDismiss }: { apiKey: NewKey; onDismiss: () => void }) {
    const [copied, setCopied] = useState(false)

    function copy() {
        navigator.clipboard.writeText(apiKey.key).then(() => {
            setCopied(true)
            setTimeout(() => setCopied(false), 2000)
        })
    }

    return (
        <div className="tok-reveal">
            <div className="tok-reveal-header">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <strong>Save your API key — it won't be shown again</strong>
            </div>
            <div className="tok-reveal-key">
                <code>{apiKey.key}</code>
                <button className="tok-copy-btn" onClick={copy} title="Copy to clipboard">
                    {copied ? (
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    ) : (
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                    )}
                    {copied ? 'Copied' : 'Copy'}
                </button>
            </div>
            {isMcpKey(apiKey) && (
                <div className="tok-mcp-config">
                    <p>
                        Connect your MCP client to <code>{MCP_URL}</code> with this key as{' '}
                        <code>Authorization: Bearer &lt;key&gt;</code> or <code>X-API-Key</code>.
                        For Claude Desktop, add this to <code>claude_desktop_config.json</code>:
                    </p>
                    <pre>{mcpConfig(apiKey.key)}</pre>
                </div>
            )}
            <button className="tok-reveal-dismiss" onClick={onDismiss}>I've saved it</button>
        </div>
    )
}

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
    const nameRef = useRef<HTMLInputElement>(null)

    function load() {
        setLoading(true)
        setError(null)
        fetch('/playground/tokens')
            .then((r) => r.json())
            .then((d: ApiKey[]) => setKeys(d))
            .catch(() => setError('Could not load API keys. Please try again.'))
            .finally(() => setLoading(false))
    }

    useEffect(() => { load() }, [])

    async function handleCreate(e: React.FormEvent) {
        e.preventDefault()
        if (!name.trim()) return

        setCreating(true)
        setCreateError(null)

        try {
            const res = await fetch('/playground/tokens', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ name: name.trim(), type }),
            })

            const data = await res.json()

            if (!res.ok) {
                setCreateError(data.message ?? 'Failed to create API key.')
                return
            }

            setNewKey(data as NewKey)
            setName('')
            setKeys((prev) => [data as ApiKey, ...prev])
        } catch {
            setCreateError('Network error. Please try again.')
        } finally {
            setCreating(false)
        }
    }

    async function handleRevoke(id: number) {
        setPendingAction(id)
        try {
            const res = await fetch(`/playground/tokens/${id}/revoke`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            })
            if (res.ok) {
                setKeys((prev) => prev.map((k) => k.id === id ? { ...k, is_active: false } : k))
            }
        } finally {
            setPendingAction(null)
        }
    }

    async function handleDelete(id: number) {
        if (!confirm('Delete this API key? This cannot be undone.')) return

        setPendingAction(id)
        try {
            const res = await fetch(`/playground/tokens/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            })
            if (res.ok) {
                setKeys((prev) => prev.filter((k) => k.id !== id))
                if (newKey?.id === id) setNewKey(null)
            }
        } finally {
            setPendingAction(null)
        }
    }

    return (
        <div className="tok-wrap">
            {/* Create form */}
            <div className="tok-create-card">
                <h2 className="tok-section-title">Generate API key</h2>
                <p className="tok-section-sub">
                    An <strong>API key</strong> calls this API; send it in the <code>X-Api-Key</code> header.
                    An <strong>MCP key</strong> connects your own MCP client (Claude Desktop, Open WebUI) to the
                    AKILIMO tools at <code>{MCP_URL}</code> and works only there.
                </p>

                <form className="tok-create-form" onSubmit={handleCreate}>
                    <select
                        className="tok-input tok-type-select"
                        value={type}
                        onChange={(e) => setType(e.target.value as KeyType)}
                        aria-label="Key type"
                    >
                        <option value="api">API key</option>
                        <option value="mcp">MCP key</option>
                    </select>
                    <input
                        ref={nameRef}
                        type="text"
                        className="tok-input"
                        placeholder="Key name, e.g. My App"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        maxLength={100}
                        required
                    />
                    <button type="submit" className="tok-create-btn" disabled={creating || !name.trim()}>
                        {creating ? (
                            <span className="fert-spinner" style={{ width: 14, height: 14 }} />
                        ) : (
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        )}
                        Generate
                    </button>
                </form>

                {createError && <p className="tok-create-error">{createError}</p>}
            </div>

            {/* New key reveal */}
            {newKey && (
                <KeyRevealBanner apiKey={newKey} onDismiss={() => setNewKey(null)} />
            )}

            {/* Key list */}
            <div className="tok-list-section">
                <div className="tok-list-header">
                    <h2 className="tok-section-title">Your API keys</h2>
                    <button className="btn btn-ghost btn--sm" onClick={load}>↻ Refresh</button>
                </div>

                {loading && (
                    <div className="hist-state">
                        <div className="fert-spinner"/>
                        Loading keys…
                    </div>
                )}

                {error && (
                    <div className="hist-state hist-state--error">
                        {error}
                        <button className="btn btn-ghost" style={{ marginTop: '0.75rem' }} onClick={load}>Retry</button>
                    </div>
                )}

                {!loading && !error && keys.length === 0 && (
                    <div className="hist-state">
                        <p>No API keys yet.</p>
                        <p style={{ fontSize: '0.875rem', color: 'var(--text-muted)' }}>
                            Use the form above to generate your first key.
                        </p>
                    </div>
                )}

                {!loading && keys.length > 0 && (
                    <div className="tok-list">
                        {keys.map((k) => (
                            <div key={k.id} className={`tok-row${!k.is_active ? ' tok-row--revoked' : ''}`}>
                                <div className="tok-row-info">
                                    <span className="tok-name">{k.name}</span>
                                    {isMcpKey(k) && <span className="tok-status tok-status--mcp">MCP</span>}
                                    <code className="tok-prefix">{k.key_prefix}…</code>
                                    <span className={`tok-status ${k.is_active ? 'tok-status--active' : 'tok-status--revoked'}`}>
                                        {k.is_active ? 'Active' : 'Revoked'}
                                    </span>
                                </div>

                                <div className="tok-row-meta">
                                    {k.last_used_at && (
                                        <span className="tok-meta-item">Used {timeAgo(k.last_used_at)}</span>
                                    )}
                                    {k.created_at && (
                                        <span className="tok-meta-item">Created {timeAgo(k.created_at)}</span>
                                    )}
                                    <span className="tok-abilities">
                                        {(k.abilities ?? ['*']).join(', ')}
                                    </span>
                                </div>

                                <div className="tok-row-actions">
                                    {k.is_active && (
                                        <button
                                            className="tok-action-btn tok-action-btn--revoke"
                                            onClick={() => handleRevoke(k.id)}
                                            disabled={pendingAction === k.id}
                                            title="Revoke key"
                                        >
                                            {pendingAction === k.id ? '…' : 'Revoke'}
                                        </button>
                                    )}
                                    <button
                                        className="tok-action-btn tok-action-btn--delete"
                                        onClick={() => handleDelete(k.id)}
                                        disabled={pendingAction === k.id}
                                        title="Delete key"
                                    >
                                        {pendingAction === k.id ? '…' : 'Delete'}
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    )
}
