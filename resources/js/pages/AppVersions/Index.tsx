import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'
import type { AppVersion } from '../../types'

interface Props { versions: AppVersion[] }

const PLATFORM_LABEL: Record<string, string> = { android: 'Android', ios: 'iOS' }

function PlatformForm({ version }: { version: AppVersion }) {
    const { data, setData, put, processing, errors, reset } = useForm({
        min_supported: String(version.min_supported),
        latest: String(version.latest),
        message: version.message ?? '',
        store_url: version.store_url ?? '',
    })
    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/admin/app-versions/${version.platform}`, { preserveScroll: true })
    }
    return (
        <ResourceForm
            title={PLATFORM_LABEL[version.platform] ?? version.platform}
            onSubmit={handleSubmit}
            processing={processing}
            onCancel={() => reset()}
        >
            <div className="row g-3">
                <div className="col-md-6">
                    <FormField label="Newest version code" required error={errors.latest}>
                        <input type="number" min={1} className={`form-control ${errors.latest ? 'is-invalid' : ''}`}
                            value={data.latest} onChange={e => setData('latest', e.target.value)} />
                    </FormField>
                    <div className="form-text mt-n2">Phones below this see a dismissible "Update available" notice.</div>
                </div>
                <div className="col-md-6">
                    <FormField label="Lowest version code allowed" required error={errors.min_supported}>
                        <input type="number" min={1} className={`form-control ${errors.min_supported ? 'is-invalid' : ''}`}
                            value={data.min_supported} onChange={e => setData('min_supported', e.target.value)} />
                    </FormField>
                    <div className="form-text mt-n2">Phones below this are blocked until they update. Raise it only to force an update.</div>
                </div>
                <div className="col-12">
                    <FormField label="Message" error={errors.message}>
                        <input type="text" maxLength={255} className={`form-control ${errors.message ? 'is-invalid' : ''}`}
                            value={data.message} onChange={e => setData('message', e.target.value)} />
                    </FormField>
                    <div className="form-text mt-n2">Optional. Replaces the app's standard wording; leave empty to use it.</div>
                </div>
                <div className="col-12">
                    <FormField label="Store link" error={errors.store_url}>
                        <input type="url" maxLength={255} className={`form-control ${errors.store_url ? 'is-invalid' : ''}`}
                            value={data.store_url} onChange={e => setData('store_url', e.target.value)} />
                    </FormField>
                </div>
            </div>
        </ResourceForm>
    )
}

export default function AppVersionsIndex({ versions }: Props) {
    return (
        <AdminLayout title="App versions">
            <div className="mx-auto d-flex flex-column gap-4" style={{ maxWidth: 720 }}>
                <p className="text-body-secondary mb-0">
                    Version codes are the build numbers of the app. The app asks for these when it opens and when it comes back
                    after a long time; being offline never blocks anyone.
                </p>
                {versions.map(v => <PlatformForm key={v.platform} version={v} />)}
            </div>
        </AdminLayout>
    )
}
