import { router, useForm } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import CountrySelect from '../../components/CountrySelect'
import FormField from '../../components/FormField'
import Pagination from '../../components/Pagination'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'
import type { AppMessage, MessageRecipient, Paginated } from '../../types'

interface Props {
    messages: Paginated<AppMessage>
    types: string[]
    targets: string[]
}

const TYPE_LABEL: Record<string, string> = { advice: 'Advice', reminder: 'Reminder', announcement: 'Announcement' }
const TARGET_LABEL: Record<string, string> = {
    hub: 'Home',
    recommendations: 'Recommendations',
    settings: 'Settings',
    inbox: 'Messages',
}

function RecipientPicker({ selected, onChange }: { selected: MessageRecipient[]; onChange: (r: MessageRecipient[]) => void }) {
    const [query, setQuery] = useState('')
    const [results, setResults] = useState<MessageRecipient[]>([])
    const [devices, setDevices] = useState<number | null>(null)

    // Search accounts (name, phone, email) and phones (device token) as the admin types
    useEffect(() => {
        if (query.trim().length < 2) { setResults([]); return }
        const timer = setTimeout(() => {
            fetch('/admin/messages/recipients?q=' + encodeURIComponent(query.trim()), { headers: { Accept: 'application/json' } })
                .then(r => r.json())
                .then((json: { data: MessageRecipient[] }) => setResults(json.data))
                .catch(() => setResults([]))
        }, 300)
        return () => clearTimeout(timer)
    }, [query])

    // How many phones the chosen recipients reach (distinct, so an account and its own phone count once)
    useEffect(() => {
        if (selected.length === 0) { setDevices(null); return }
        const params = new URLSearchParams()
        selected.forEach((r, i) => { params.append(`recipients[${i}][type]`, r.type); params.append(`recipients[${i}][id]`, r.id) })
        fetch('/admin/messages/audience?' + params.toString(), { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then((json: { devices: number }) => setDevices(json.devices))
            .catch(() => setDevices(null))
    }, [selected])

    const isSelected = (r: MessageRecipient) => selected.some(s => s.type === r.type && s.id === r.id)

    return (
        <div>
            <input type="search" className="form-control" placeholder="Search a farmer by name, phone or email, or a device token"
                value={query} onChange={e => setQuery(e.target.value)} />
            {results.length > 0 && (
                <ul className="list-group mt-2">
                    {results.map(r => (
                        <li key={`${r.type}:${r.id}`} className="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div className="fw-semibold">{r.label}</div>
                                <div className="small text-body-secondary">{r.detail || '—'} · {r.devices} phone{r.devices === 1 ? '' : 's'}</div>
                            </div>
                            <button type="button" className="btn btn-sm btn-outline-primary" disabled={isSelected(r)}
                                onClick={() => { onChange([...selected, r]); setQuery(''); setResults([]) }}>
                                {isSelected(r) ? 'Added' : 'Add'}
                            </button>
                        </li>
                    ))}
                </ul>
            )}
            {selected.length > 0 && (
                <div className="mt-3">
                    <div className="d-flex flex-wrap gap-2">
                        {selected.map(r => (
                            <span key={`${r.type}:${r.id}`} className="badge text-bg-secondary d-inline-flex align-items-center gap-2">
                                {r.label}
                                <button type="button" className="btn-close btn-close-white" style={{ fontSize: '0.6rem' }} aria-label={`Remove ${r.label}`}
                                    onClick={() => onChange(selected.filter(s => !(s.type === r.type && s.id === r.id)))} />
                            </span>
                        ))}
                    </div>
                    <div className={`form-text ${devices === 0 ? 'text-danger' : ''}`}>
                        {devices === null ? 'Counting phones…' : devices === 0
                            ? 'No phone is registered for push yet. The message is stored and reaches them the next time they open the app.'
                            : `This will be pushed to ${devices} phone${devices === 1 ? '' : 's'}.`}
                    </div>
                </div>
            )}
        </div>
    )
}

function SendForm({ types, targets }: Pick<Props, 'types' | 'targets'>) {
    const [mode, setMode] = useState<'audience' | 'recipients'>('audience')
    const { data, setData, post, processing, errors, reset, transform } = useForm({
        type: 'announcement',
        title: '',
        body: '',
        country: '',
        language: '',
        target: '',
        expires_at: '',
        recipients: [] as MessageRecipient[],
    })
    // Only the type and id of a recipient go to the server; a private message has no country or language
    transform(d => mode === 'recipients'
        ? { ...d, country: '', language: '', recipients: d.recipients.map(r => ({ type: r.type, id: r.id })) }
        : { ...d, recipients: [] })
    const recipientError = Object.entries(errors).find(([k]) => k.startsWith('recipients'))?.[1]
    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/admin/messages', { preserveScroll: true, onSuccess: () => reset() })
    }
    return (
        <ResourceForm title="Send a message" onSubmit={handleSubmit} processing={processing} onCancel={() => reset()}>
            <div className="row g-3">
                <div className="col-md-6">
                    <FormField label="Type" required error={errors.type}>
                        <select className={`form-select ${errors.type ? 'is-invalid' : ''}`}
                            value={data.type} onChange={e => setData('type', e.target.value)}>
                            {types.map(t => <option key={t} value={t}>{TYPE_LABEL[t] ?? t}</option>)}
                        </select>
                    </FormField>
                </div>
                <div className="col-md-6">
                    <FormField label="Opens in the app" error={errors.target}>
                        <select className={`form-select ${errors.target ? 'is-invalid' : ''}`}
                            value={data.target} onChange={e => setData('target', e.target.value)}>
                            <option value="">Default for the type</option>
                            {targets.map(t => <option key={t} value={t}>{TARGET_LABEL[t] ?? t}</option>)}
                        </select>
                    </FormField>
                </div>
                <div className="col-12">
                    <FormField label="Title" required error={errors.title}>
                        <input type="text" maxLength={120} className={`form-control ${errors.title ? 'is-invalid' : ''}`}
                            value={data.title} onChange={e => setData('title', e.target.value)} />
                    </FormField>
                </div>
                <div className="col-12">
                    <FormField label="Message" required error={errors.body}>
                        <textarea rows={4} maxLength={2000} className={`form-control ${errors.body ? 'is-invalid' : ''}`}
                            value={data.body} onChange={e => setData('body', e.target.value)} />
                    </FormField>
                </div>
                <div className="col-12">
                    <div className="btn-group" role="group" aria-label="Who gets the message">
                        <button type="button" className={`btn btn-sm ${mode === 'audience' ? 'btn-primary' : 'btn-outline-primary'}`}
                            onClick={() => setMode('audience')}>Everyone in a country or language</button>
                        <button type="button" className={`btn btn-sm ${mode === 'recipients' ? 'btn-primary' : 'btn-outline-primary'}`}
                            onClick={() => setMode('recipients')}>Specific farmers or phones</button>
                    </div>
                </div>
                {mode === 'recipients' && (
                    <div className="col-12">
                        <FormField label="Recipients" error={recipientError}>
                            <RecipientPicker selected={data.recipients} onChange={r => setData('recipients', r)} />
                        </FormField>
                    </div>
                )}
                {mode === 'audience' && <>
                <div className="col-md-4">
                    <FormField label="Country" error={errors.country}>
                        <CountrySelect value={data.country} onChange={c => setData('country', c)} error={errors.country} />
                    </FormField>
                </div>
                <div className="col-md-4">
                    <FormField label="Language" error={errors.language}>
                        <select className={`form-select ${errors.language ? 'is-invalid' : ''}`}
                            value={data.language} onChange={e => setData('language', e.target.value)}>
                            <option value="">Any language</option>
                            <option value="en">English</option>
                            <option value="sw">Swahili</option>
                            <option value="rw">Kinyarwanda</option>
                        </select>
                    </FormField>
                </div>
                </>}
                <div className="col-md-4">
                    <FormField label="Stop showing after" error={errors.expires_at}>
                        <input type="datetime-local" className={`form-control ${errors.expires_at ? 'is-invalid' : ''}`}
                            value={data.expires_at} onChange={e => setData('expires_at', e.target.value)} />
                    </FormField>
                </div>
                <div className="col-12 form-text mt-0">
                    {mode === 'audience'
                        ? 'Pick a country, a language, or both. With neither, phones get the message only the next time they open the app (a push needs at least one). Write the text in the language you choose.'
                        : 'A private message is pushed to every phone registered for the chosen farmers or devices, and only they can fetch it.'}
                </div>
            </div>
        </ResourceForm>
    )
}

export default function MessagesIndex({ messages, types, targets }: Props) {
    const { data, meta, links } = messages
    return (
        <AdminLayout title="Messages">
            <div className="d-flex flex-column gap-4">
                <div className="mx-auto w-100" style={{ maxWidth: 720 }}>
                    <SendForm types={types} targets={targets} />
                </div>
                <div className="card shadow-sm">
                    <div className="card-header bg-white py-3"><h5 className="mb-0 fw-semibold">Sent messages</h5></div>
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead>
                                <tr><th>Sent</th><th>Type</th><th>Message</th><th>Audience</th><th>Push</th></tr>
                            </thead>
                            <tbody>
                                {data.length === 0 && (
                                    <tr><td colSpan={5} className="text-center text-body-secondary py-4">No messages yet.</td></tr>
                                )}
                                {data.map(m => (
                                    <tr key={m.id}>
                                        <td className="text-nowrap">{m.created_at ? new Date(m.created_at).toLocaleString() : '—'}</td>
                                        <td><span className="badge bg-secondary">{TYPE_LABEL[m.type] ?? m.type}</span></td>
                                        <td style={{ maxWidth: 360 }}>
                                            <div className="fw-semibold">{m.title}</div>
                                            <div className="small text-body-secondary text-truncate">{m.body}</div>
                                        </td>
                                        <td>{m.recipient ?? `${m.country ?? 'All countries'} · ${m.language ?? 'all languages'}`}</td>
                                        <td>{m.pushed_at ? <span className="badge bg-success">Sent</span> : <span className="badge bg-light text-dark border">Not pushed</span>}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="card-footer bg-white">
                        <Pagination meta={meta} links={links}
                            onPageChange={page => router.get('/admin/messages', { page }, { preserveScroll: true })} />
                    </div>
                </div>
            </div>
        </AdminLayout>
    )
}
