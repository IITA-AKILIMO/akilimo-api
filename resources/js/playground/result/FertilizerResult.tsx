import {fmt, fmtDate, pctChange} from './format'
import {Footnote, MetricCard, Section, changeClass, type Tone} from './parts'
import type {FertilizerRate, FRData} from './types'

/** Fertilizer (FR) and intercropping (IC) results: yields, money, nutrients and the bag rates. */
export default function FertilizerResult({data, rates, country}: Readonly<{data: FRData; rates: FertilizerRate[]; country: string}>) {
    const yieldChange = pctChange(data.CurrentY, data.TargetY)
    const nrPositive = data.NR >= 0
    const maxRate = rates?.length ? Math.max(...rates.map((x) => x.rate)) : 0

    return (
        <div>
            <div className="row g-3">
                <MetricCard icon="🌾" label="Current Yield" tone="secondary"
                            value={`${data.CurrentY?.toFixed(1) ?? '—'} t/ha`} sub="estimated"/>
                <MetricCard icon="🎯" label="Target Yield" tone="success"
                            value={`${data.TargetY?.toFixed(1) ?? '—'} t/ha`}
                            sub={yieldChange
                                ? <span className={changeClass(yieldChange.positive)}>{yieldChange.value} vs current</span>
                                : 'with recommendations'}/>
                <MetricCard icon="💰" label="Total Input Cost" tone="warning"
                            value={fmt(data.TC ?? 0, country)} sub="fertilizer cost"/>
                <MetricCard icon={nrPositive ? '📈' : '📉'} label="Net Revenue Gain"
                            tone={(nrPositive ? 'success' : 'danger') satisfies Tone}
                            value={fmt(data.NR ?? 0, country)} sub="vs no fertilizer"/>
            </div>

            {(data.N || data.P || data.K) ? (
                <Section title="Nutrient application">
                    <div className="d-flex flex-wrap align-items-center gap-2">
                        <NutrientBadge letter="N" value={data.N} tone="success"/>
                        <NutrientBadge letter="P" value={data.P} tone="danger"/>
                        <NutrientBadge letter="K" value={data.K} tone="warning"/>
                        {data.WLY ? (
                            <span className="small text-body-secondary ms-2">
                                Water-limited yield potential: <strong>{data.WLY.toFixed(1)} t/ha</strong>
                            </span>
                        ) : null}
                    </div>
                </Section>
            ) : null}

            {rates && rates.length > 0 && (
                <Section title="Recommended fertilizer application">
                    <ul className="list-unstyled mb-0">
                        {rates.map((r) => (
                            <li key={r.type} className="row g-2 align-items-center mb-2">
                                <span className="col-4 col-sm-3 small fw-semibold text-truncate">{r.type}</span>
                                <span className="col">
                                    <progress className="pg-bar" value={r.rate} max={maxRate} aria-label={`${r.type} ${r.rate} kg`}/>
                                </span>
                                <span className="col-auto small fw-semibold text-end pg-bar-value">{r.rate} kg</span>
                            </li>
                        ))}
                    </ul>
                </Section>
            )}

            {data.plDate && (
                <Footnote>
                    Computed for planting date: <strong>{fmtDate(data.plDate)}</strong>
                    {' '}· Location: {data.lat?.toFixed(3)}, {data.lon?.toFixed(3)}
                </Footnote>
            )}
        </div>
    )
}

function NutrientBadge({letter, value, tone}: Readonly<{letter: string; value: number; tone: Tone}>) {
    return (
        <span className={`badge fs-6 fw-normal border border-${tone}-subtle text-${tone}-emphasis bg-${tone}-subtle`}>
            <strong className="me-1">{letter}</strong>{value ?? 0} kg
        </span>
    )
}
