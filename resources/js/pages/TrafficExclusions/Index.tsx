import { router, useForm } from '@inertiajs/react'
import { useState, type FormEvent } from 'react'
import ConfirmDialog from '../../components/ConfirmDialog'
import TableFilters from '../../components/TableFilters'
import AdminLayout from '../../layouts/AdminLayout'

type Field = 'phone' | 'name' | 'device'
type MatchType = 'exact' | 'starts_with' | 'contains' | 'regex'

interface Rule {
    id: number
    field: Field
    match_type: MatchType
    value: string
    note: string | null
    active: boolean
    matches: number
}

interface Props {
    rules: Rule[]
    fields: Record<Field, string>
    matchTypes: Record<MatchType, string>
    totals: { requests: number; excluded: number }
    filters: Record<string, string>
}

const PLACEHOLDERS: Record<Field, Record<MatchType, string>> = {
    phone: { exact: '254700000000', starts_with: '254', contains: '700123', regex: '^\\+?49' },
    name: { exact: 'Jane Doe', starts_with: 'Test', contains: 'KREYE', regex: '^test[0-9]+$' },
    device: { exact: 'a1b2c3d4-…', starts_with: 'emulator-', contains: 'test', regex: '^qa-' },
}

const HINTS: Record<Field, string> = {
    phone: 'Compared as digits only, so +254 700 123, 254700123 and 254-700-123 are the same. Patterns use the number as stored.',
    name: 'Checks the full name and the user name, ignoring case.',
    device: 'The device ID the app sends with each request, ignoring case.',
}

export default function TrafficExclusionsIndex({ rules, fields, matchTypes, totals, filters }: Props) {
    const filtered = Object.values(filters).some((v) => v !== '')
    const form = useForm<{ field: Field; match_type: MatchType; value: string; note: string }>({
        field: 'phone',
        match_type: 'starts_with',
        value: '',
        note: '',
    })
    const [deleting, setDeleting] = useState<Rule | null>(null)
    const [busy, setBusy] = useState<number | null>(null)

    function submit(e: FormEvent) {
        e.preventDefault()
        form.post('/admin/traffic-exclusions', { preserveScroll: true, onSuccess: () => form.reset('value', 'note') })
    }

    function toggle(rule: Rule) {
        setBusy(rule.id)
        router.patch(`/admin/traffic-exclusions/${rule.id}`, { active: !rule.active }, {
            preserveScroll: true,
            onFinish: () => setBusy(null),
        })
    }

    function remove() {
        if (!deleting) return
        setBusy(deleting.id)
        router.delete(`/admin/traffic-exclusions/${deleting.id}`, {
            preserveScroll: true,
            onFinish: () => { setBusy(null); setDeleting(null) },
        })
    }

    const pct = totals.requests > 0 ? ((totals.excluded / totals.requests) * 100).toFixed(1) : '0'

    return (
        <AdminLayout title="Excluded Traffic">
            <p className="text-body-secondary mb-4" style={{ maxWidth: '48rem' }}>
                Requests that match an active rule count as staff or test traffic and are left out of the dashboard and
                reports. They are still stored and shown in the request log. Changes apply to all requests at once;
                the dashboard catches up within 15 minutes, or at once with its Refresh button.
            </p>

            <div className="row g-4 mb-4">
                <div className="col-lg-4">
                    <div className="card shadow-sm h-100">
                        <div className="card-body">
                            <p className="text-muted small fw-medium text-uppercase mb-1">Excluded now</p>
                            <p className="fs-3 fw-bold mb-1">{totals.excluded.toLocaleString()}</p>
                            <p className="text-muted small mb-0">
                                {pct}% of {totals.requests.toLocaleString()} requests
                            </p>
                        </div>
                    </div>
                </div>

                <div className="col-lg-8">
                    <form className="card shadow-sm h-100" onSubmit={submit} noValidate>
                        <div className="card-header bg-white py-3">
                            <h2 className="h6 mb-0 fw-semibold">Add a rule</h2>
                        </div>
                        <div className="card-body">
                            <div className="row g-2 align-items-start">
                                <div className="col-sm-4">
                                    <label htmlFor="rule-field" className="form-label small mb-1">Check</label>
                                    <select
                                        id="rule-field"
                                        className="form-select form-select-sm"
                                        value={form.data.field}
                                        onChange={(e) => form.setData('field', e.target.value as Field)}
                                    >
                                        {Object.entries(fields).map(([k, label]) => <option key={k} value={k}>{label}</option>)}
                                    </select>
                                </div>
                                <div className="col-sm-4 col-xl-3">
                                    <label htmlFor="rule-match" className="form-label small mb-1">Match</label>
                                    <select
                                        id="rule-match"
                                        className="form-select form-select-sm"
                                        value={form.data.match_type}
                                        onChange={(e) => form.setData('match_type', e.target.value as MatchType)}
                                    >
                                        {Object.entries(matchTypes).map(([k, label]) => <option key={k} value={k}>{label}</option>)}
                                    </select>
                                </div>
                                <div className="col-sm-4 col-xl-5">
                                    <label htmlFor="rule-value" className="form-label small mb-1">Value</label>
                                    <input
                                        id="rule-value"
                                        className={`form-control form-control-sm${form.errors.value ? ' is-invalid' : ''}`}
                                        value={form.data.value}
                                        onChange={(e) => form.setData('value', e.target.value)}
                                        placeholder={PLACEHOLDERS[form.data.field][form.data.match_type]}
                                        aria-describedby="rule-hint"
                                        required
                                    />
                                    {form.errors.value && <div className="invalid-feedback">{form.errors.value}</div>}
                                </div>
                                <div className="col-12">
                                    <label htmlFor="rule-note" className="form-label small mb-1">Note <span className="text-muted">(optional)</span></label>
                                    <input
                                        id="rule-note"
                                        className="form-control form-control-sm"
                                        value={form.data.note}
                                        onChange={(e) => form.setData('note', e.target.value)}
                                        placeholder="Whose number this is, or why it is excluded"
                                        maxLength={255}
                                    />
                                </div>
                            </div>
                            <div className="d-flex flex-wrap align-items-center gap-2 mt-3">
                                <p id="rule-hint" className="small text-muted mb-0 flex-grow-1">{HINTS[form.data.field]}</p>
                                <button type="submit" className="btn btn-primary btn-sm" disabled={form.processing || !form.data.value.trim()}>
                                    {form.processing ? 'Saving…' : 'Add rule'}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <TableFilters url="/admin/traffic-exclusions" filters={filters} total={rules.length} defs={[
                { type: 'search', key: 'search', placeholder: 'Search value or note…' },
                { type: 'select', key: 'field', label: 'Checks', options: Object.entries(fields).map(([value, label]) => ({ value, label })) },
                { type: 'active', key: 'active' },
            ]} />

            <div className="card shadow-sm">
                <div className="card-header bg-white py-3">
                    <h2 className="h6 mb-0 fw-semibold">Rules</h2>
                </div>
                {rules.length ? (
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="table-light">
                                <tr>
                                    <th className="px-3 py-2">Rule</th>
                                    <th className="px-3 py-2">Note</th>
                                    <th className="px-3 py-2 text-end">Matches</th>
                                    <th className="px-3 py-2">Active</th>
                                    <th className="px-3 py-2"><span className="visually-hidden">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                {rules.map((rule) => (
                                    <tr key={rule.id} className={rule.active ? undefined : 'text-body-secondary'}>
                                        <td className="px-3 py-2">
                                            <span className="small">{fields[rule.field]} {matchTypes[rule.match_type]}</span>{' '}
                                            <code className="text-break">{rule.value}</code>
                                        </td>
                                        <td className="px-3 py-2 small">{rule.note || '—'}</td>
                                        <td className="px-3 py-2 small text-end">{rule.matches.toLocaleString()}</td>
                                        <td className="px-3 py-2">
                                            <div className="form-check form-switch mb-0">
                                                <input
                                                    id={`rule-active-${rule.id}`}
                                                    className="form-check-input"
                                                    type="checkbox"
                                                    role="switch"
                                                    checked={rule.active}
                                                    disabled={busy === rule.id}
                                                    onChange={() => toggle(rule)}
                                                />
                                                <label className="form-check-label small" htmlFor={`rule-active-${rule.id}`}>
                                                    {rule.active ? 'On' : 'Off'}
                                                </label>
                                            </div>
                                        </td>
                                        <td className="px-3 py-2 text-end">
                                            <button
                                                type="button"
                                                className="btn btn-outline-danger btn-sm"
                                                onClick={() => setDeleting(rule)}
                                                disabled={busy === rule.id}
                                                aria-label={`Delete rule ${fields[rule.field]} ${matchTypes[rule.match_type]} ${rule.value}`}
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="card-body text-center text-muted py-4">
                        {filtered ? 'No rule matches these filters.' : 'No rules: every request counts as real traffic.'}
                    </div>
                )}
            </div>

            <ConfirmDialog
                open={!!deleting}
                title="Delete rule"
                message={deleting ? `Delete "${fields[deleting.field]} ${matchTypes[deleting.match_type]} ${deleting.value}"? Requests it matched will count as real traffic again. To keep it for later, switch it off instead.` : ''}
                onConfirm={remove}
                onCancel={() => setDeleting(null)}
                processing={busy === deleting?.id}
            />
        </AdminLayout>
    )
}
