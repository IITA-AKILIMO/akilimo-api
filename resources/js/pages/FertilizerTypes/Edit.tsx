import { Link, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'

interface Item { type: string; label: string | null; name: string; weight: number; category: string }
interface Props { item: Item; categories: { value: string; label: string }[]; countries: string[] }

export default function FertilizerTypesEdit({ item, categories, countries }: Props) {
    const { data, setData, put, processing, errors } = useForm({ name: item.name, weight: String(item.weight), category: item.category })

    function handleSubmit(e: FormEvent) { e.preventDefault(); put(`/admin/fertilizer-types/${encodeURIComponent(item.type)}`) }

    return (
        <AdminLayout title="Edit Fertilizer">
            <div className="mx-auto" style={{ maxWidth: 640 }}>
                <ResourceForm title={`Edit fertilizer: ${item.name}`} onSubmit={handleSubmit} processing={processing} onCancel={() => window.history.back()}>
                    <p className="text-body-secondary small">
                        A change here applies in every country that sells this fertilizer{countries.length ? ` (${countries.join(', ')})` : ''}.
                        The code and the catalogue label cannot be changed, because keys and prices are built from them.
                        To change how one country sells it, edit it from the <Link href="/admin/fertilizers">fertilizers list</Link>.
                    </p>
                    <div className="row g-3">
                        <div className="col-md-8">
                            <FormField label="Name" required error={errors.name}>
                                <input type="text" maxLength={255} className={`form-control ${errors.name ? 'is-invalid' : ''}`} value={data.name} onChange={e => setData('name', e.target.value)} />
                            </FormField>
                        </div>
                        <div className="col-md-4">
                            <FormField label="Bag weight (kg)" required error={errors.weight}>
                                <input type="number" min={1} max={1000} className={`form-control ${errors.weight ? 'is-invalid' : ''}`} value={data.weight} onChange={e => setData('weight', e.target.value)} />
                            </FormField>
                        </div>
                        <div className="col-md-12">
                            <FormField label="Category" required error={errors.category}>
                                <select className={`form-select ${errors.category ? 'is-invalid' : ''}`} value={data.category} onChange={e => setData('category', e.target.value)}>
                                    {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                                </select>
                            </FormField>
                        </div>
                        <div className="col-md-6">
                            <FormField label="Code"><input type="text" className="form-control" value={item.type} readOnly /></FormField>
                        </div>
                        <div className="col-md-6">
                            <FormField label="Catalogue label"><input type="text" className="form-control" value={item.label ?? ''} readOnly /></FormField>
                        </div>
                    </div>
                </ResourceForm>
            </div>
        </AdminLayout>
    )
}
