import {fmt, fmtDate, weekOffset} from './format'
import {BestCard, Section, changeClass, signed} from './parts'
import type {SPRow} from './types'

/** Planting schedule (SP): the best plant/harvest window and every window compared. */
export default function PlantingScheduleResult({rows, country}: Readonly<{rows: SPRow[]; country: string}>) {
    const best = rows[0]

    return (
        <div>
            {best && (
                <BestCard label="Optimal planting window" tone="info">
                    <div className="d-flex align-items-center gap-3 mb-3">
                        <DateBlock icon="🌱" label="Plant" date={fmtDate(best.PD)}/>
                        <span className="fs-4 text-body-secondary" aria-hidden="true">→</span>
                        <DateBlock icon="🌾" label="Harvest" date={fmtDate(best.HD)}/>
                    </div>
                    <div className="d-flex flex-wrap column-gap-4 row-gap-1 small">
                        <span>Estimated gross revenue: <strong className="text-success">{fmt(best.GR, country)}</strong></span>
                        {best.dGR !== 0 && (
                            <span>Change vs current: <strong className={changeClass(best.dGR >= 0)}>
                                {signed(best.dGR, fmt(best.dGR, country))}
                            </strong></span>
                        )}
                        {best.rootUP > 0 && (
                            <span>Cassava price used: <strong>{fmt(best.rootUP, country)}/1000 kg</strong></span>
                        )}
                    </div>
                </BestCard>
            )}

            {rows.length > 1 && (
                <Section title="All planting windows compared">
                    <div className="table-responsive">
                        <table className="table table-sm align-middle mb-0">
                            <thead>
                            <tr>
                                <th scope="col">Plant date</th>
                                <th scope="col">Harvest date</th>
                                <th scope="col">Planting shift</th>
                                <th scope="col">Harvest shift</th>
                                <th scope="col">Gross revenue</th>
                                <th scope="col">Change</th>
                                <th scope="col"><span className="visually-hidden">Marker</span></th>
                            </tr>
                            </thead>
                            <tbody>
                            {rows.map((r, i) => (
                                <tr key={i} className={r.CP ? 'table-active' : ''}>
                                    <td className="text-nowrap">{fmtDate(r.PD)}</td>
                                    <td className="text-nowrap">{fmtDate(r.HD)}</td>
                                    <td className="small text-body-secondary">{weekOffset(r.rPWnr)}</td>
                                    <td className="small text-body-secondary">{weekOffset(r.rHWnr)}</td>
                                    <td className="text-success">{fmt(r.GR, country)}</td>
                                    <td className={changeClass(r.dGR >= 0)}>{signed(r.dGR, fmt(r.dGR, country))}</td>
                                    <td>
                                        {r.CP && <span className="badge text-bg-info">Your dates</span>}
                                        {!r.CP && i === 0 && <span className="badge text-bg-success">Best</span>}
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                </Section>
            )}
        </div>
    )
}

function DateBlock({icon, label, date}: Readonly<{icon: string; label: string; date: string}>) {
    return (
        <div className="text-center">
            <div className="fs-4" aria-hidden="true">{icon}</div>
            <div className="small text-body-secondary">{label}</div>
            <div className="fw-bold">{date}</div>
        </div>
    )
}
