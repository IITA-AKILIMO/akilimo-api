import { Link, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import CountrySelect from '../../components/CountrySelect'
import AdminLayout from '../../layouts/AdminLayout'
import type { FertilizerTypeOption, FertilizerUseCase } from '../../types'

interface Props { types: FertilizerTypeOption[]; useCases: FertilizerUseCase[] }

export default function FertilizersCreate({ types, useCases }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        type: '', country: '', sort_order: '', use_case: '',
        cis: false, cim: false, available: true,
    })

    const selected = types.find((t) => t.type === data.type)
    // The key is the type and the country; it is computed, never typed
    const key = data.type && data.country ? `${data.type}_${data.country}` : ''

    function handleSubmit(e: FormEvent) { e.preventDefault(); post('/admin/fertilizers') }

    return (
        <AdminLayout title="New Fertilizer">
            <div className="mx-auto" style={{ maxWidth: 640 }}>
                <ResourceForm title="Sell a fertilizer in a country" onSubmit={handleSubmit} processing={processing} onCancel={() => window.history.back()}>
                    <p className="text-body-secondary small">
                        Pick the fertilizer and the country. Its name and bag weight come from the fertilizer, and its key is
                        worked out from the fertilizer and the country. Not in the list?{' '}
                        <Link href="/admin/fertilizer-types/create">Add a new fertilizer</Link>.
                    </p>
                    <div className="row g-3">
                        <div className="col-md-6">
                            <FormField label="Fertilizer" required error={errors.type}>
                                <select className={`form-select ${errors.type ? 'is-invalid' : ''}`} value={data.type} onChange={e => setData('type', e.target.value)}>
                                    <option value="">Select fertilizer</option>
                                    {types.map((t) => <option key={t.type} value={t.type}>{t.name}</option>)}
                                </select>
                            </FormField>
                        </div>
                        <div className="col-md-6">
                            <FormField label="Country" required error={errors.country}>
                                <CountrySelect value={data.country} onChange={(v) => setData('country', v)} error={errors.country} required />
                            </FormField>
                        </div>
                        <div className="col-md-6">
                            <FormField label="Fertilizer key">
                                <input type="text" className="form-control" value={key} readOnly placeholder="Worked out from the fertilizer and country" />
                            </FormField>
                        </div>
                        <div className="col-md-3">
                            <FormField label="Bag weight (kg)">
                                <input type="text" className="form-control" value={selected ? String(selected.weight) : ''} readOnly />
                            </FormField>
                        </div>
                        <div className="col-md-3">
                            <FormField label="Sort Order" error={errors.sort_order}>
                                <input type="number" min={0} className="form-control" value={data.sort_order} onChange={e => setData('sort_order', e.target.value)} />
                            </FormField>
                        </div>
                        <div className="col-md-6">
                            <FormField label="Use Case" required error={errors.use_case}>
                                <select className={`form-select ${errors.use_case ? 'is-invalid' : ''}`} value={data.use_case} onChange={e => setData('use_case', e.target.value)}>
                                    <option value="">Select use case</option>
                                    {useCases.map((u) => <option key={u.value} value={u.value}>{u.value} — {u.label}</option>)}
                                </select>
                            </FormField>
                        </div>
                        <div className="col-12">
                            <div className="d-flex gap-4">
                                <div className="form-check"><input type="checkbox" className="form-check-input" id="available" checked={data.available} onChange={e => setData('available', e.target.checked)} /><label className="form-check-label" htmlFor="available">Available</label></div>
                                <div className="form-check"><input type="checkbox" className="form-check-input" id="cis" checked={data.cis} onChange={e => setData('cis', e.target.checked)} /><label className="form-check-label" htmlFor="cis">CIS</label></div>
                                <div className="form-check"><input type="checkbox" className="form-check-input" id="cim" checked={data.cim} onChange={e => setData('cim', e.target.checked)} /><label className="form-check-label" htmlFor="cim">CIM</label></div>
                            </div>
                        </div>
                    </div>
                </ResourceForm>
            </div>
        </AdminLayout>
    )
}
