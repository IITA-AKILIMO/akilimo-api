import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'
import type { Country, PriceBasis } from '../../types'

interface Props {
    item: Country
    basisOptions: { value: PriceBasis; label: string }[]
    currencies: { value: string; label: string }[]
}

export default function CountriesEdit({ item, basisOptions, currencies }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        code: item.code,
        name: item.name,
        active: item.active ?? true,
        currency_code: item.currency_code ?? '',
        default_price_basis: (item.default_price_basis ?? 'open_market') as PriceBasis,
        sort_order: item.sort_order != null ? String(item.sort_order) : '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/admin/countries/${item.id}`)
    }

    return (
        <AdminLayout title="Edit Country">
            <div className="mx-auto" style={{ maxWidth: 480 }}>
                <ResourceForm
                    title={`Edit ${item.name}`}
                    onSubmit={handleSubmit}
                    processing={processing}
                    onCancel={() => window.history.back()}
                >
                    <div className="row g-3">
                        <div className="col-md-3">
                            <FormField label="Code" required error={errors.code}>
                                <input
                                    type="text"
                                    maxLength={2}
                                    className={`form-control text-uppercase ${errors.code ? 'is-invalid' : ''}`}
                                    value={data.code}
                                    onChange={(e) => setData('code', e.target.value.toUpperCase())}
                                />
                            </FormField>
                        </div>
                        <div className="col-md-9">
                            <FormField label="Country Name" required error={errors.name}>
                                <input
                                    type="text"
                                    maxLength={100}
                                    className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                />
                            </FormField>
                        </div>
                        <div className="col-md-4">
                            <FormField label="Currency" error={errors.currency_code}>
                                <select className={`form-select ${errors.currency_code ? 'is-invalid' : ''}`} value={data.currency_code} onChange={(e) => setData('currency_code', e.target.value)}>
                                    <option value="">None</option>
                                    {currencies.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                                </select>
                            </FormField>
                        </div>
                        <div className="col-md-4">
                            <FormField label="Default Price Basis" error={errors.default_price_basis}>
                                <select
                                    className="form-select"
                                    value={data.default_price_basis}
                                    onChange={(e) => setData('default_price_basis', e.target.value as PriceBasis)}
                                >
                                    {basisOptions.map((b) => (
                                        <option key={b.value} value={b.value}>{b.label}</option>
                                    ))}
                                </select>
                            </FormField>
                        </div>
                        <div className="col-md-4">
                            <FormField label="Sort Order" error={errors.sort_order}>
                                <input
                                    type="number"
                                    min={0}
                                    step={1}
                                    className={`form-control ${errors.sort_order ? 'is-invalid' : ''}`}
                                    value={data.sort_order}
                                    onChange={(e) => setData('sort_order', e.target.value)}
                                />
                            </FormField>
                        </div>
                        <div className="col-md-8 d-flex align-items-end pb-1">
                            <div className="form-check">
                                <input
                                    type="checkbox"
                                    className="form-check-input"
                                    id="active"
                                    checked={data.active}
                                    onChange={(e) => setData('active', e.target.checked)}
                                />
                                <label className="form-check-label" htmlFor="active">Active (visible in dropdowns)</label>
                            </div>
                        </div>
                    </div>
                </ResourceForm>
            </div>
        </AdminLayout>
    )
}
