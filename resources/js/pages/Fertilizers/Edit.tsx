import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import CountrySelect from '../../components/CountrySelect'
import AdminLayout from '../../layouts/AdminLayout'
import type { Fertilizer, FertilizerTypeOption, FertilizerUseCase } from '../../types'

interface Props { item: Fertilizer; types: FertilizerTypeOption[]; useCases: FertilizerUseCase[] }

export default function FertilizersEdit({ item, types, useCases }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        type: item.type, country: item.country, sort_order: String(item.sort_order ?? ''),
        use_case: item.use_case, cis: item.cis, cim: item.cim, available: item.available,
    })

    const selected = types.find((t) => t.type === data.type)
    // The key is the type and the country; it is computed, never typed
    const key = data.type && data.country ? `${data.type}_${data.country}` : ''

    function handleSubmit(e: FormEvent) { e.preventDefault(); put(`/admin/fertilizers/${item.id}`) }

    return (
        <AdminLayout title="Edit Fertilizer">
            <div className="mx-auto" style={{ maxWidth: 640 }}>
                <ResourceForm title={`Edit: ${item.name} (${item.country})`} onSubmit={handleSubmit} processing={processing} onCancel={() => window.history.back()}>
                    <div className="row g-3">
                        <div className="col-md-6">
                            <FormField label="Fertilizer" required error={errors.type}>
                                <select className={`form-select ${errors.type ? 'is-invalid' : ''}`} value={data.type} onChange={e => setData('type', e.target.value)}>
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
                                <input type="text" className="form-control" value={key} readOnly />
                            </FormField>
                        </div>
                        <div className="col-md-3">
                            <FormField label="Bag weight (kg)">
                                <input type="text" className="form-control" value={selected ? String(selected.weight) : String(item.weight)} readOnly />
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
                                    {!useCases.some((u) => u.value === data.use_case) && <option value={data.use_case}>{data.use_case} (not in list)</option>}
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
