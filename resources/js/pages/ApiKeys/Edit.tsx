import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'
import type { ApiKey } from '../../types'

interface User {
    id: number
    name: string
    email: string
    role: string
}

interface Props {
    apiKey: ApiKey & { user_id: number }
    users: User[]
    abilities: string[]
    abilityGroups: Record<string, string[]>
    abilityLabels: Record<string, string>
    rolePresets: Record<string, string[]>
    // False for partners: they cannot create wildcard keys
    canWildcard: boolean
}

interface FormData {
    user_id: number
    name: string
    abilities: string[]
    expires_at: string
    is_active: boolean
}

const PRESET_STYLE: Record<string, string> = {
    user:       'btn-outline-secondary',
    partner:    'btn-outline-primary',
    admin:      'btn-outline-danger',
}

export default function ApiKeysEdit({ apiKey, users, abilityGroups, abilityLabels, rolePresets, canWildcard }: Props) {
    const { data, setData, put, processing, errors } = useForm<FormData>({
        user_id:    apiKey.user_id,
        name:       apiKey.name,
        abilities:  apiKey.abilities || [],
        expires_at: apiKey.expires_at ? apiKey.expires_at.split('T')[0] : '',
        is_active:  apiKey.is_active,
    })

    function toggleAbility(ability: string) {
        const current = data.abilities
        setData('abilities', current.includes(ability)
            ? current.filter((a) => a !== ability)
            : [...current, ability])
    }

    function applyPreset(preset: string) {
        setData('abilities', rolePresets[preset] ?? [])
    }

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/admin/api-keys/${apiKey.id}`)
    }

    return (
        <AdminLayout title="Edit API Key">
            <div className="mx-auto" style={{ maxWidth: 640 }}>
                <ResourceForm
                    title="Edit API Key"
                    onSubmit={handleSubmit}
                    processing={processing}
                    onCancel={() => window.history.back()}
                >
                    <div className="alert alert-info d-flex align-items-center mb-4">
                        <i className="bi bi-info-circle me-2"></i>
                        <div>
                            <strong>Key Prefix:</strong> <code>{apiKey.key_prefix}****</code>
                            <br />
                            <small className="text-muted">
                                The full key cannot be retrieved. Create a new key if you need a new one.
                            </small>
                        </div>
                    </div>

                    <FormField label="User" required error={errors.user_id}>
                        <select
                            value={data.user_id}
                            onChange={(e) => setData('user_id', parseInt(e.target.value))}
                            className={`form-select ${errors.user_id ? 'is-invalid' : ''}`}
                        >
                            <option value="">Select a user…</option>
                            {users.map((user) => (
                                <option key={user.id} value={user.id}>
                                    {user.name} ({user.email}) — {user.role}
                                </option>
                            ))}
                        </select>
                    </FormField>

                    <FormField label="Key Name" required error={errors.name}>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                            placeholder="e.g., Production API"
                        />
                    </FormField>

                    <FormField label="Abilities" error={errors.abilities}>
                        {/* Role preset quick-fill */}
                        <div className="d-flex flex-wrap gap-2 mb-3">
                            <span className="text-muted small align-self-center me-1">Quick-fill:</span>
                            {Object.keys(rolePresets).map((preset) => (
                                <button
                                    key={preset}
                                    type="button"
                                    className={`btn btn-sm ${PRESET_STYLE[preset] ?? 'btn-outline-secondary'}`}
                                    onClick={() => applyPreset(preset)}
                                >
                                    {preset.charAt(0).toUpperCase() + preset.slice(1)}
                                </button>
                            ))}
                            <button
                                type="button"
                                className="btn btn-sm btn-outline-secondary"
                                onClick={() => setData('abilities', [])}
                            >
                                {canWildcard ? 'Clear (wildcard)' : 'Clear'}
                            </button>
                        </div>

                        {/* Grouped ability checkboxes */}
                        <div className="d-flex flex-column gap-3">
                            {Object.entries(abilityGroups).map(([group, groupAbilities]) => (
                                <div key={group}>
                                    <p className="text-muted small text-uppercase fw-semibold mb-2" style={{ letterSpacing: '0.05em' }}>
                                        {group}
                                    </p>
                                    <div className="d-flex flex-column gap-1">
                                        {groupAbilities.map((ability) => (
                                            <div key={ability} className="form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    id={`ability-${ability}`}
                                                    checked={data.abilities.includes(ability)}
                                                    onChange={() => toggleAbility(ability)}
                                                />
                                                <label className="form-check-label" htmlFor={`ability-${ability}`}>
                                                    <code className="me-2 text-secondary" style={{ fontSize: '0.8em' }}>{ability}</code>
                                                    <span className="text-muted small">{abilityLabels[ability]}</span>
                                                </label>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="form-text mt-2">
                            {canWildcard ? <>Leave empty for wildcard access (<code>*</code>).</> : <>Choose at least one ability.</>}
                            {data.abilities.length > 0 && (
                                <> Selected: <strong>{data.abilities.join(', ')}</strong></>
                            )}
                        </div>
                    </FormField>

                    <FormField label="Expiry Date" error={errors.expires_at}>
                        <input
                            type="date"
                            value={data.expires_at}
                            onChange={(e) => setData('expires_at', e.target.value)}
                            className={`form-control ${errors.expires_at ? 'is-invalid' : ''}`}
                        />
                        <div className="form-text">Leave empty for no expiration.</div>
                    </FormField>

                    <FormField label="Status">
                        <div className="form-check form-switch">
                            <input
                                type="checkbox"
                                className="form-check-input"
                                id="is_active"
                                checked={data.is_active}
                                onChange={(e) => setData('is_active', e.target.checked)}
                            />
                            <label className="form-check-label" htmlFor="is_active">
                                {data.is_active ? 'Active' : 'Revoked'}
                            </label>
                        </div>
                    </FormField>
                </ResourceForm>
            </div>
        </AdminLayout>
    )
}
