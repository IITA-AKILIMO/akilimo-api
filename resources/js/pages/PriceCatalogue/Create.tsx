import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import PriceCatalogueFields, { type PriceCatalogueFormData } from '../../components/PriceCatalogueFields'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'
import type { PriceLookups } from '../../types'

interface Props extends PriceLookups {
    statuses: string[]
    confidenceLevels: string[]
    defaultBagKg: number
}

export default function PriceCatalogueCreate({ confidenceLevels, defaultBagKg, currencies, units, currencyByCountry, items, fertilizerTypes }: Props) {
    const { data, setData, post, processing, errors } = useForm<PriceCatalogueFormData>({
        country: '', item: '', region: '', price: '', subsidised_price: '',
        unit: 'per_bag', bag_kg: String(defaultBagKg), currency: '',
        min_price: '', max_price: '', source: '', source_date: '',
        confidence: '', effective_from: '', fertilizer_type: '',
    })

    function handleSubmit(e: FormEvent) { e.preventDefault(); post('/admin/price-catalogue') }

    return (
        <AdminLayout title="New Catalogue Price">
            <div className="mx-auto" style={{ maxWidth: 820 }}>
                <ResourceForm title="Add a price to the catalogue" onSubmit={handleSubmit} processing={processing} onCancel={() => window.history.back()}>
                    <p className="text-body-secondary small">
                        New rows start as <strong>draft</strong>. A reviewer activates them from the list once the source has been checked.
                    </p>
                    <PriceCatalogueFields
                        creating
                        data={data}
                        setField={(key, value) => setData(key, value)}
                        errors={errors}
                        confidenceLevels={confidenceLevels}
                        defaultBagKg={defaultBagKg}
                        currencies={currencies}
                        units={units}
                        currencyByCountry={currencyByCountry}
                        items={items}
                        fertilizerTypes={fertilizerTypes}
                    />
                </ResourceForm>
            </div>
        </AdminLayout>
    )
}
