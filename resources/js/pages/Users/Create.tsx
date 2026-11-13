import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'
import type { UserRole } from '../../types'

interface Props {
    countries: { code: string; name: string }[]
}

interface FormData {
    name: string
    username: string
    email: string
    role: UserRole
    reviewer_countries: string[]
    password: string
    password_confirmation: string
}

const ROLE_OPTIONS: { value: UserRole; label: string; description: string }[] = [
    { value: 'user',       label: 'User',       description: 'Can compute recommendations only' },
    { value: 'partner',    label: 'Partner',    description: 'Can compute, read history, submit prices & feedback' },
    { value: 'admin',      label: 'Admin',      description: 'Full access to all resources' },
]

export default function UsersCreate({ countries }: Props) {
    const { data, setData, post, processing, errors } = useForm<FormData>({
        name: '',
        username: '',
        email: '',
        role: 'user',
        reviewer_countries: [],
        password: '',
        password_confirmation: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/admin/users')
    }

    function toggleReviewerCountry(code: string) {
        setData('reviewer_countries', data.reviewer_countries.includes(code)
            ? data.reviewer_countries.filter((c) => c !== code)
            : [...data.reviewer_countries, code])
    }

    return (
        <AdminLayout title="New User">
            <div className="mx-auto" style={{ maxWidth: 640 }}>
                <ResourceForm
                    title="Create User"
                    onSubmit={handleSubmit}
                    processing={processing}
                    onCancel={() => window.history.back()}
                >
                    <FormField label="Name" required error={errors.name}>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                            placeholder="Full name"
                        />
                    </FormField>

                    <FormField label="Username" required error={errors.username}>
                        <input
                            type="text"
                            value={data.username}
                            onChange={(e) => setData('username', e.target.value)}
                            className={`form-control ${errors.username ? 'is-invalid' : ''}`}
                            placeholder="username"
                            autoComplete="username"
                        />
                    </FormField>

                    <FormField label="Email" required error={errors.email}>
                        <input
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                            placeholder="user@example.com"
                            autoComplete="email"
                        />
                    </FormField>

                    <FormField label="Role" required error={errors.role}>
                        <select
                            value={data.role}
                            onChange={(e) => setData('role', e.target.value as UserRole)}
                            className={`form-select ${errors.role ? 'is-invalid' : ''}`}
                        >
                            {ROLE_OPTIONS.map((r) => (
                                <option key={r.value} value={r.value}>{r.label}</option>
                            ))}
                        </select>
                        <div className="form-text">
                            {ROLE_OPTIONS.find((r) => r.value === data.role)?.description}
                        </div>
                    </FormField>

                    <div className="p-3 bg-light rounded border mb-3">
                        <p className="text-muted small text-uppercase fw-semibold mb-1" style={{ letterSpacing: '0.05em' }}>
                            Price Reviewer
                        </p>
                        <p className="text-body-secondary small mb-3">
                            Tick the countries this user may review and activate catalogue prices for. Leave all
                            unticked for no reviewer access.
                        </p>
                        {countries.length === 0 ? (
                            <p className="text-body-secondary small mb-0">No countries configured yet.</p>
                        ) : (
                            <div className="row g-2">
                                {countries.map((c) => (
                                    <div className="col-6 col-md-4" key={c.code}>
                                        <div className="form-check">
                                            <input
                                                type="checkbox"
                                                className="form-check-input"
                                                id={`reviewer-${c.code}`}
                                                checked={data.reviewer_countries.includes(c.code)}
                                                onChange={() => toggleReviewerCountry(c.code)}
                                            />
                                            <label className="form-check-label" htmlFor={`reviewer-${c.code}`}>
                                                {c.code} — {c.name}
                                            </label>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                        {errors.reviewer_countries && (
                            <div className="text-danger small mt-2">{errors.reviewer_countries}</div>
                        )}
                    </div>

                    <FormField label="Password" required error={errors.password}>
                        <input
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                            autoComplete="new-password"
                        />
                    </FormField>

                    <FormField label="Confirm Password" required error={errors.password_confirmation}>
                        <input
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            className={`form-control ${errors.password_confirmation ? 'is-invalid' : ''}`}
                            autoComplete="new-password"
                        />
                    </FormField>
                </ResourceForm>
            </div>
        </AdminLayout>
    )
}
