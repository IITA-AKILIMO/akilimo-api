import CountrySelect from './CountrySelect'
import CurrencySelect from './CurrencySelect'
import DateInput, { todayISO } from './DateInput'
import FormField from './FormField'
import FormSection from './FormSection'
import ItemSelect from './ItemSelect'
import UnitSelect from './UnitSelect'
import type { PriceLookups } from '../types'

export interface PriceCatalogueFormData {
    country: string
    item: string
    region: string
    price: string
    subsidised_price: string
    unit: string
    bag_kg: string
    currency: string
    min_price: string
    max_price: string
    source: string
    source_date: string
    confidence: string
    effective_from: string
    fertilizer_type: string
}

interface Props extends PriceLookups {
    data: PriceCatalogueFormData
    setField: (key: keyof PriceCatalogueFormData, value: string) => void
    errors: Partial<Record<keyof PriceCatalogueFormData, string>>
    confidenceLevels: string[]
    defaultBagKg: number
    /** Placeholders that suit a new row. */
    creating?: boolean
}

/**
 * The fields of a catalogue price, in four groups, for both the create and the edit screen. The rules that
 * tie fields together live here once: the country fills the currency, the item fills the unit and the
 * fertiliser, and a unit other than per-bag has no bag size or fertiliser.
 */
export default function PriceCatalogueFields({ data, setField, errors, confidenceLevels, defaultBagKg, currencies, units, currencyByCountry, items, fertilizerTypes, creating }: Props) {
    const perBag = data.unit === 'per_bag'

    function changeUnit(unit: string) {
        setField('unit', unit)

        if (unit !== 'per_bag') {
            setField('bag_kg', '')
            setField('fertilizer_type', '')
        } else if (!data.bag_kg) {
            setField('bag_kg', String(defaultBagKg))
        }
    }

    // Choosing the country fills in its currency, unless one was already chosen by hand
    function changeCountry(code: string) {
        const previous = currencyByCountry[data.country]
        setField('country', code)

        if (currencyByCountry[code] && (!data.currency || data.currency === previous)) {
            setField('currency', currencyByCountry[code])
        }
    }

    // Choosing a known item fills in the unit it is priced in and, for a fertilizer, its type
    function changeItem(name: string) {
        setField('item', name)
        const unit = items.find((i) => i.item === name)?.unit

        if (unit) {
            changeUnit(unit)
        }

        const type = fertilizerTypes.find((t) => (t.label ?? '').toLowerCase() === name.toLowerCase())?.type

        if (type) {
            setField('fertilizer_type', type)
        }
    }

    const text = (key: keyof PriceCatalogueFormData, props: { maxLength?: number; placeholder?: string } = {}) => (
        <input type="text" className={`form-control ${errors[key] ? 'is-invalid' : ''}`} value={data[key]} onChange={(e) => setField(key, e.target.value)} {...props} />
    )

    const money = (key: keyof PriceCatalogueFormData) => (
        <input type="number" min={0} step="0.01" className={`form-control ${errors[key] ? 'is-invalid' : ''}`} value={data[key]} onChange={(e) => setField(key, e.target.value)} />
    )

    return (
        <>
            <FormSection title="What is priced" hint="The item, the country it is priced in, and the fertilizer it is for when it is a bag of fertilizer.">
                <div className="row g-3">
                    <div className="col-md-4">
                        <FormField label="Country" required error={errors.country}>
                            <CountrySelect value={data.country} onChange={changeCountry} error={errors.country} required />
                        </FormField>
                    </div>
                    <div className="col-md-8">
                        <FormField label="Item" required error={errors.item}>
                            <ItemSelect value={data.item} onChange={changeItem} items={items} error={errors.item} required />
                        </FormField>
                    </div>
                    <div className="col-md-6">
                        <FormField label="Region" error={errors.region}>
                            {text('region', { maxLength: 50, placeholder: 'Blank = national' })}
                        </FormField>
                    </div>
                    <div className="col-md-6">
                        <FormField label="Fertiliser" error={errors.fertilizer_type}>
                            <select className={`form-select ${errors.fertilizer_type ? 'is-invalid' : ''}`} value={data.fertilizer_type} onChange={(e) => setField('fertilizer_type', e.target.value)} disabled={!perBag}>
                                <option value="">{perBag ? 'Select fertilizer' : 'Fertilisers only'}</option>
                                {fertilizerTypes.map((t) => <option key={t.type} value={t.type}>{t.name}</option>)}
                            </select>
                        </FormField>
                    </div>
                </div>
            </FormSection>

            <FormSection title="Price" hint="The open-market price, the programme price farmers pay where there is a subsidy, and the unit it is for.">
                <div className="row g-3">
                    <div className="col-md-4">
                        <FormField label="Open-market price" required error={errors.price}>{money('price')}</FormField>
                    </div>
                    <div className="col-md-4">
                        <FormField label="Subsidised price" error={errors.subsidised_price}>{money('subsidised_price')}</FormField>
                    </div>
                    <div className="col-md-4">
                        <FormField label="Currency" error={errors.currency}>
                            <CurrencySelect value={data.currency} onChange={(v) => setField('currency', v)} currencies={currencies} error={errors.currency} />
                        </FormField>
                    </div>
                    <div className="col-md-6">
                        <FormField label="Unit" required error={errors.unit}>
                            <UnitSelect value={data.unit} onChange={changeUnit} units={units} error={errors.unit} required />
                        </FormField>
                    </div>
                    <div className="col-md-6">
                        <FormField label="Bag size (kg)" error={errors.bag_kg}>
                            <input type="number" min={1} max={1000} className={`form-control ${errors.bag_kg ? 'is-invalid' : ''}`} value={data.bag_kg} onChange={(e) => setField('bag_kg', e.target.value)} disabled={!perBag} />
                        </FormField>
                    </div>
                </div>
            </FormSection>

            <FormSection title="Allowed range" hint="Optional. The lowest and highest price a client should accept.">
                <div className="row g-3">
                    <div className="col-md-6">
                        <FormField label="Min allowed price" error={errors.min_price}>{money('min_price')}</FormField>
                    </div>
                    <div className="col-md-6">
                        <FormField label="Max allowed price" error={errors.max_price}>{money('max_price')}</FormField>
                    </div>
                </div>
            </FormSection>

            <FormSection title="Source and dates" hint="Where the price comes from and from when it applies.">
                <div className="row g-3">
                    <div className="col-12">
                        <FormField label="Source" error={errors.source}>
                            {text('source', { maxLength: 255, placeholder: creating ? 'e.g. TFRA notice, Sep 2025' : undefined })}
                        </FormField>
                    </div>
                    <div className="col-md-6">
                        <FormField label="Source date" error={errors.source_date}>
                            <DateInput value={data.source_date} onChange={(v) => setField('source_date', v)} error={errors.source_date} max={todayISO()} />
                        </FormField>
                    </div>
                    <div className="col-md-6">
                        <FormField label="Confidence" error={errors.confidence}>
                            <select className={`form-select ${errors.confidence ? 'is-invalid' : ''}`} value={data.confidence} onChange={(e) => setField('confidence', e.target.value)}>
                                <option value="">Unset</option>
                                {confidenceLevels.map((c) => <option key={c} value={c}>{c}</option>)}
                            </select>
                        </FormField>
                    </div>
                    <div className="col-md-6">
                        <FormField label="Effective from" error={errors.effective_from}>
                            <DateInput value={data.effective_from} onChange={(v) => setField('effective_from', v)} error={errors.effective_from} />
                        </FormField>
                    </div>
                </div>
            </FormSection>
        </>
    )
}
