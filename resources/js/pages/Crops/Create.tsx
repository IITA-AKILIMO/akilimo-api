import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'

export default function CropsCreate() {
    const { data, setData, post, processing, errors } = useForm({
        crop_key: '', name: '', description: '', active: true, advice_available: false, countries: '', sort_order: 0,
    })
    function handleSubmit(e: FormEvent) { e.preventDefault(); post('/admin/crops') }
    return (
        <AdminLayout title="New Crop">
            <div className="mx-auto" style={{ maxWidth: 640 }}>
                <ResourceForm title="Create Crop" onSubmit={handleSubmit} processing={processing} onCancel={() => window.history.back()}>
                    <div className="row g-3">
                        <div className="col-md-4"><FormField label="Key" required error={errors.crop_key}><input type="text" maxLength={50} placeholder="maize" className={`form-control ${errors.crop_key ? 'is-invalid' : ''}`} value={data.crop_key} onChange={e => setData('crop_key', e.target.value.toLowerCase())} /></FormField></div>
                        <div className="col-md-8"><FormField label="Name" required error={errors.name}><input type="text" className={`form-control ${errors.name ? 'is-invalid' : ''}`} value={data.name} onChange={e => setData('name', e.target.value)} /></FormField></div>
                        <div className="col-12"><FormField label="Description" error={errors.description}><input type="text" className="form-control" value={data.description} onChange={e => setData('description', e.target.value)} /></FormField></div>
                        <div className="col-md-8"><FormField label="Countries" error={errors.countries}><input type="text" placeholder="NG, TZ (empty = every country)" className="form-control" value={data.countries} onChange={e => setData('countries', e.target.value)} /></FormField></div>
                        <div className="col-md-4"><FormField label="Order" error={errors.sort_order}><input type="number" min={0} className="form-control" value={data.sort_order} onChange={e => setData('sort_order', Number(e.target.value))} /></FormField></div>
                        <div className="col-md-6"><div className="form-check form-switch"><input id="crop-active" className="form-check-input" type="checkbox" checked={data.active} onChange={e => setData('active', e.target.checked)} /><label className="form-check-label" htmlFor="crop-active">Active (listed to clients)</label></div></div>
                        <div className="col-md-6"><div className="form-check form-switch"><input id="crop-advice" className="form-check-input" type="checkbox" checked={data.advice_available} onChange={e => setData('advice_available', e.target.checked)} /><label className="form-check-label" htmlFor="crop-advice">Recommendations available</label></div></div>
                    </div>
                </ResourceForm>
            </div>
        </AdminLayout>
    )
}
