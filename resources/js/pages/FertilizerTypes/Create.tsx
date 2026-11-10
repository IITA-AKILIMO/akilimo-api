import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'

/** The code a name will get, as the server works it out: upper case letters and digits joined by underscores */
function previewCode(name: string): string {
    return name
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .toUpperCase().replace(/[^A-Z0-9]+/g, '_').replace(/^_+|_+$/g, '')
}

/** The catalogue label a name will get when none is typed: its letters and digits */
function previewLabel(name: string): string {
    return name.normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^A-Za-z0-9]+/g, '')
}

export default function FertilizerTypesCreate() {
    const { data, setData, post, processing, errors } = useForm({ name: '', weight: '50', label: '' })

    function handleSubmit(e: FormEvent) { e.preventDefault(); post('/admin/fertilizer-types') }

    const code = previewCode(data.name)

    return (
        <AdminLayout title="New Fertilizer">
            <div className="mx-auto" style={{ maxWidth: 640 }}>
                <ResourceForm title="Add a new fertilizer" onSubmit={handleSubmit} processing={processing} onCancel={() => window.history.back()}>
                    <p className="text-body-secondary small">
                        Give the name and the usual bag weight. The code that every fertilizer key is built from is worked out from the
                        name and cannot be changed afterwards. You choose the countries that sell it next.
                    </p>
                    <div className="row g-3">
                        <div className="col-md-8">
                            <FormField label="Name" required error={errors.name}>
                                <input type="text" maxLength={255} className={`form-control ${errors.name ? 'is-invalid' : ''}`} value={data.name} onChange={e => setData('name', e.target.value)} placeholder="e.g. NPK 20:10:10" />
                            </FormField>
                        </div>
                        <div className="col-md-4">
                            <FormField label="Bag weight (kg)" required error={errors.weight}>
                                <input type="number" min={1} max={1000} className={`form-control ${errors.weight ? 'is-invalid' : ''}`} value={data.weight} onChange={e => setData('weight', e.target.value)} />
                            </FormField>
                        </div>
                        <div className="col-md-6">
                            <FormField label="Code (worked out)">
                                <input type="text" className="form-control" value={code} readOnly placeholder="Worked out from the name" />
                            </FormField>
                        </div>
                        <div className="col-md-6">
                            <FormField label="Catalogue label" error={errors.label}>
                                <input type="text" maxLength={50} className={`form-control ${errors.label ? 'is-invalid' : ''}`} value={data.label} onChange={e => setData('label', e.target.value)} placeholder={previewLabel(data.name) || 'Defaults from the name'} />
                            </FormField>
                        </div>
                    </div>
                </ResourceForm>
            </div>
        </AdminLayout>
    )
}
