import { router, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import PriceCatalogueFields, { type PriceCatalogueFormData } from '../../components/PriceCatalogueFields'
import ResourceForm from '../../components/ResourceForm'
import AdminLayout from '../../layouts/AdminLayout'
import type { PriceCatalogue, PriceCatalogueHistory, PriceLookups, PriceStatus } from '../../types'

interface Props extends PriceLookups {
    item: PriceCatalogue
    history: PriceCatalogueHistory[]
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

export default function PriceCatalogueEdit({ item, history, currentUserId, confidenceLevels, defaultBagKg, currencies, units, items, currencyByCountry, fertilizerTypes }: Props) {
    const { data, setData, put, processing, errors } = useForm<PriceCatalogueFormData>({
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
        fertilizer_type: item.fertilizer_type ?? '',
    })

    const status = (item.status ?? 'draft') as PriceStatus

    function handleSubmit(e: FormEvent) { e.preventDefault(); put(`/admin/price-catalogue/${item.id}`) }

    function activate() {
        router.post(`/admin/price-catalogue/${item.id}/activate`, {}, { preserveScroll: true })
    }

    function confirm() {
        router.post(`/admin/price-catalogue/${item.id}/confirm`, {}, { preserveScroll: true })
    }

    return (
        <AdminLayout title="Edit Catalogue Price">
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
                    <PriceCatalogueFields
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
