import { router, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import FormField from '../../components/FormField'
import ResourceForm from '../../components/ResourceForm'
import CountrySelect from '../../components/CountrySelect'
import CurrencySelect from '../../components/CurrencySelect'
import ItemSelect from '../../components/ItemSelect'
import UnitSelect from '../../components/UnitSelect'
import AdminLayout from '../../layouts/AdminLayout'
import type { DefaultPrice, DefaultPriceHistory, PriceLookups, PriceStatus } from '../../types'

interface Props extends PriceLookups {
    item: DefaultPrice
    history: DefaultPriceHistory[]
    currentUserId?: number | null
    confidenceLevels: string[]
    defaultBagKg: number
}

const STATUS_STYLES: Record<PriceStatus, string> = {
    active: 'bg-success',
    draft: 'bg-secondary',
    pending_second_review: 'bg-warning text-dark',
}

const STATUS_LABELS: Record<PriceStatus, string> = {
    active: 'Active',
    draft: 'Draft',
    pending_second_review: 'Pending second review',
}

function str(value: unknown): string {
    return value === null || value === undefined ? '' : String(value)
}

export default function DefaultPricesEdit({ item, history, currentUserId, confidenceLevels, defaultBagKg, currencies, units, items, currencyByCountry }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        country: item.country,
        item: item.item,
        region: item.region ?? '',
        price: str(item.price),
        subsidised_price: str(item.subsidised_price),
        unit: item.unit,
        bag_kg: str(item.bag_kg ?? (item.unit === 'per_bag' ? defaultBagKg : '')),
        currency: item.currency ?? '',
        min_price: str(item.min_price),
        max_price: str(item.max_price),
        source: item.source ?? '',
        source_date: item.source_date ?? '',
        confidence: item.confidence ?? '',
        effective_from: item.effective_from ?? '',
        fertilizer_key: item.fertilizer_key ?? '',
    })

    const status = (item.status ?? 'draft') as PriceStatus

    function handleSubmit(e: FormEvent) { e.preventDefault(); put(`/admin/default-prices/${item.id}`) }

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
    function changeItem(name: string) {
        setData('item', name)
        const unit = items.find((i) => i.item === name)?.unit
        if (unit) changeUnit(unit)
    }

    function activate() {
        router.post(`/admin/default-prices/${item.id}/activate`, {}, { preserveScroll: true })
    }

    function confirm() {
        router.post(`/admin/default-prices/${item.id}/confirm`, {}, { preserveScroll: true })
    }

    return (
        <AdminLayout title="Edit Default Price">
            <div className="mx-auto" style={{ maxWidth: 820 }}>
                <div className="d-flex align-items-center justify-content-between mb-3">
                    <div className="d-flex align-items-center gap-2">
                        <span className="text-body-secondary">Status</span>
                        <span className={`badge ${STATUS_STYLES[status] ?? 'bg-secondary'}`}>{STATUS_LABELS[status] ?? status}</span>
                    </div>
                    <div className="d-flex gap-2">
                        {status === 'draft' && (
                            <button type="button" className="btn btn-success btn-sm" onClick={activate}>Activate</button>
                        )}
                        {status === 'pending_second_review' && item.submitted_by !== currentUserId && (
                            <button type="button" className="btn btn-success btn-sm" onClick={confirm}>Confirm</button>
                        )}
                    </div>
                </div>

                <ResourceForm title={`Edit: ${item.item} (${item.country})`} onSubmit={handleSubmit} processing={processing} onCancel={() => window.history.back()}>
                    <p className="text-body-secondary small">
                        Saving puts the row back into <strong>draft</strong>; activate it again after review.
                        The previous values stay in the history below.
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
                        <div className="col-md-4"><FormField label="Fertiliser key" error={errors.fertilizer_key}><input type="text" maxLength={50} className="form-control" value={data.fertilizer_key} onChange={e => setData('fertilizer_key', e.target.value.toUpperCase())} placeholder="e.g. UREA_TZ" disabled={data.unit !== 'per_bag'} /></FormField></div>
                        <div className="col-md-4"><FormField label="Effective from" error={errors.effective_from}><input type="date" className="form-control" value={data.effective_from} onChange={e => setData('effective_from', e.target.value)} /></FormField></div>

                        <div className="col-md-4"><FormField label="Min allowed price" error={errors.min_price}><input type="number" min={0} step="0.01" className="form-control" value={data.min_price} onChange={e => setData('min_price', e.target.value)} /></FormField></div>
                        <div className="col-md-4"><FormField label="Max allowed price" error={errors.max_price}><input type="number" min={0} step="0.01" className="form-control" value={data.max_price} onChange={e => setData('max_price', e.target.value)} /></FormField></div>
                        <div className="col-md-4"><FormField label="Confidence" error={errors.confidence}><select className="form-select" value={data.confidence} onChange={e => setData('confidence', e.target.value)}><option value="">Unset</option>{confidenceLevels.map(c => <option key={c} value={c}>{c}</option>)}</select></FormField></div>

                        <div className="col-md-8"><FormField label="Source" error={errors.source}><input type="text" maxLength={255} className="form-control" value={data.source} onChange={e => setData('source', e.target.value)} /></FormField></div>
                        <div className="col-md-4"><FormField label="Source date" error={errors.source_date}><input type="date" className="form-control" value={data.source_date} onChange={e => setData('source_date', e.target.value)} /></FormField></div>
                    </div>
                </ResourceForm>

                <div className="card border-0 shadow-sm mt-4">
                    <div className="card-header bg-white border-bottom py-3">
                        <h6 className="mb-0 fw-semibold">History</h6>
                    </div>
                    <div className="table-responsive">
                        <table className="table table-sm align-middle mb-0">
                            <thead className="table-light">
                            <tr>
                                <th>When</th>
                                <th>Action</th>
                                <th>Price</th>
                                <th>Subsidised</th>
                                <th>Status</th>
                                <th>By</th>
                                <th>Note</th>
                            </tr>
                            </thead>
                            <tbody>
                            {history.length === 0 ? (
                                <tr><td colSpan={7} className="text-center text-muted py-4">No changes recorded yet.</td></tr>
                            ) : history.map((h) => {
                                const next = h.new_values ?? {}
                                const prev = h.old_values ?? {}
                                return (
                                    <tr key={h.id}>
                                        <td className="text-nowrap small">{h.created_at}</td>
                                        <td><span className="badge bg-secondary">{h.action}</span></td>
                                        <td>{str(prev.price)}{prev.price !== undefined && next.price !== undefined && prev.price !== next.price ? ' → ' : ''}{prev.price !== next.price ? str(next.price) : ''}</td>
                                        <td>{str(prev.subsidised_price)}{prev.subsidised_price !== next.subsidised_price ? ' → ' : ''}{prev.subsidised_price !== next.subsidised_price ? str(next.subsidised_price) : ''}</td>
                                        <td>{str(prev.status)}{prev.status !== next.status ? ' → ' : ''}{prev.status !== next.status ? str(next.status) : ''}</td>
                                        <td className="small">{h.changed_by ?? '—'}</td>
                                        <td className="small">{h.note ?? '—'}</td>
                                    </tr>
                                )
                            })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    )
}
