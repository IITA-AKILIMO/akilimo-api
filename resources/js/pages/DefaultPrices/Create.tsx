import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import CountrySelect from '../../components/CountrySelect'
import CurrencySelect from '../../components/CurrencySelect'
import ItemSelect from '../../components/ItemSelect'
import UnitSelect from '../../components/UnitSelect'
import AdminLayout from '../../layouts/AdminLayout'
import type { PriceLookups } from '../../types'

interface Props extends PriceLookups {
    statuses: string[]
    confidenceLevels: string[]
    defaultBagKg: number
}

export default function DefaultPricesCreate({ confidenceLevels, defaultBagKg, currencies, units, currencyByCountry, items }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        country: '', item: '', region: '', price: '', subsidised_price: '',
        unit: 'per_bag', bag_kg: String(defaultBagKg), currency: '',
        min_price: '', max_price: '', source: '', source_date: '',
        confidence: '', effective_from: '', fertilizer_key: '',
    })

    function handleSubmit(e: FormEvent) { e.preventDefault(); post('/admin/default-prices') }

    function changeUnit(unit: string) {
        setData('unit', unit)
        if (unit !== 'per_bag') setData('bag_kg', '')
        else if (!data.bag_kg) setData('bag_kg', String(defaultBagKg))
    }

    // Choosing the country fills in its currency, unless one was already chosen by hand
    function changeCountry(code: string) {
        const previous = currencyByCountry[data.country]
        setData('country', code)
        if (currencyByCountry[code] && (!data.currency || data.currency === previous)) {
            setData('currency', currencyByCountry[code])
        }
    }

    // Choosing a known item fills in the unit it is priced in
    function changeItem(item: string) {
        setData('item', item)
        const unit = items.find((i) => i.item === item)?.unit
        if (unit) changeUnit(unit)
    }

    return (
        <AdminLayout title="New Default Price">
            <div className="mx-auto" style={{ maxWidth: 820 }}>
                <ResourceForm title="Create Default Price" onSubmit={handleSubmit} processing={processing} onCancel={() => window.history.back()}>
                    <p className="text-body-secondary small">
                        New rows start as <strong>draft</strong>. A reviewer activates them from the list once the source has been checked.
                    </p>
                    <div className="row g-3">
                        <div className="col-md-3"><FormField label="Country" required error={errors.country}><CountrySelect value={data.country} onChange={changeCountry} error={errors.country} required /></FormField></div>
                        <div className="col-md-5"><FormField label="Item" required error={errors.item}><ItemSelect value={data.item} onChange={changeItem} items={items} error={errors.item} required /></FormField></div>
                        <div className="col-md-4"><FormField label="Region" error={errors.region}><input type="text" maxLength={50} className="form-control" value={data.region} onChange={e => setData('region', e.target.value)} placeholder="Blank = national" /></FormField></div>

                        <div className="col-md-4"><FormField label="Open-market price" required error={errors.price}><input type="number" min={0} step="0.01" className={`form-control ${errors.price ? 'is-invalid' : ''}`} value={data.price} onChange={e => setData('price', e.target.value)} /></FormField></div>
                        <div className="col-md-4"><FormField label="Subsidised price" error={errors.subsidised_price}><input type="number" min={0} step="0.01" className="form-control" value={data.subsidised_price} onChange={e => setData('subsidised_price', e.target.value)} /></FormField></div>
                        <div className="col-md-4"><FormField label="Currency" error={errors.currency}><CurrencySelect value={data.currency} onChange={(v) => setData('currency', v)} currencies={currencies} error={errors.currency} /></FormField></div>

                        <div className="col-md-4"><FormField label="Unit" required error={errors.unit}><UnitSelect value={data.unit} onChange={changeUnit} units={units} error={errors.unit} required /></FormField></div>
                        <div className="col-md-4"><FormField label="Bag size (kg)" error={errors.bag_kg}><input type="number" min={1} max={1000} className="form-control" value={data.bag_kg} onChange={e => setData('bag_kg', e.target.value)} disabled={data.unit !== 'per_bag'} /></FormField></div>
                        <div className="col-md-4"><FormField label="Effective from" error={errors.effective_from}><input type="date" className="form-control" value={data.effective_from} onChange={e => setData('effective_from', e.target.value)} /></FormField></div>

                        <div className="col-md-4"><FormField label="Min allowed price" error={errors.min_price}><input type="number" min={0} step="0.01" className="form-control" value={data.min_price} onChange={e => setData('min_price', e.target.value)} /></FormField></div>
                        <div className="col-md-4"><FormField label="Max allowed price" error={errors.max_price}><input type="number" min={0} step="0.01" className="form-control" value={data.max_price} onChange={e => setData('max_price', e.target.value)} /></FormField></div>
                        <div className="col-md-4"><FormField label="Confidence" error={errors.confidence}><select className="form-select" value={data.confidence} onChange={e => setData('confidence', e.target.value)}><option value="">Unset</option>{confidenceLevels.map(c => <option key={c} value={c}>{c}</option>)}</select></FormField></div>

                        <div className="col-md-4"><FormField label="Fertiliser key" error={errors.fertilizer_key}><input type="text" maxLength={50} className="form-control" value={data.fertilizer_key} onChange={e => setData('fertilizer_key', e.target.value.toUpperCase())} placeholder="e.g. UREA_TZ (fertilisers only)" /></FormField></div>

                        <div className="col-md-8"><FormField label="Source" error={errors.source}><input type="text" maxLength={255} className="form-control" value={data.source} onChange={e => setData('source', e.target.value)} placeholder="e.g. TFRA notice, Sep 2025" /></FormField></div>
                        <div className="col-md-4"><FormField label="Source date" error={errors.source_date}><input type="date" className="form-control" value={data.source_date} onChange={e => setData('source_date', e.target.value)} /></FormField></div>
                    </div>
                </ResourceForm>
            </div>
        </AdminLayout>
    )
}
