import type { PreviewResult } from '../types'

/** Laravel's CSRF token for a plain fetch: the XSRF-TOKEN cookie, sent back as a header */
function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/)
    return match ? decodeURIComponent(match[1]) : ''
}

export type PreviewOutcome =
    | { kind: 'ok'; result: PreviewResult }
    | { kind: 'blocked'; message: string }
    | { kind: 'invalid' | 'failed'; message: string }

/**
 * Ask the server what a change would do to the app's price lists. Nothing is saved:
 * the server makes the change, reads the lists, and takes it back.
 */
export async function requestPreview(url: string, body: Record<string, unknown> = {}): Promise<PreviewOutcome> {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': xsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify(body),
        })
        const json = await response.json()

        if (response.status === 422) {
            const first = Object.values((json.errors ?? {}) as Record<string, string[]>)[0]?.[0]
            return { kind: 'invalid', message: first ?? 'Some values are not valid yet.' }
        }
        if (!response.ok) return { kind: 'failed', message: 'The preview could not be worked out.' }
        if (json.blocked) return { kind: 'blocked', message: String(json.message) }

        return { kind: 'ok', result: json as PreviewResult }
    } catch {
        return { kind: 'failed', message: 'The preview could not be worked out.' }
    }
}

/** What a change would do, list by list, with each row's value now and after. */
export function PreviewPanel({ result, verb = 'change' }: { result: PreviewResult; verb?: string }) {
    if (result.changed === 0) {
        return <div className="alert alert-secondary small mt-3 mb-0" role="status">No value in any price list would change.</div>
    }

    return (
        <div className="mt-3 text-body" role="status" aria-live="polite">
            <p className="fw-semibold mb-2">
                This would {verb} {result.changed} value{result.changed === 1 ? '' : 's'} in the app&apos;s price lists.
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
