import {fmt, methodLabel} from './format'
import {BestCard, Footnote, Section, changeClass, signed} from './parts'
import type {PPRow} from './types'

/** Planting practices (PP): the recommended land preparation and how every option compares. */
export default function PlantingPracticesResult({rows, country}: Readonly<{rows: PPRow[]; country: string}>) {
    const recommended = rows.find((r) => !r.CP) ?? rows[0]
    const current = rows.find((r) => r.CP)
    const recommendedIndex = rows.findIndex((x) => !x.CP)

    return (
        <div>
            {recommended && (
                <BestCard label="Recommended approach">
                    <div className="d-flex flex-wrap gap-2 mb-3">
                        {recommended.ploughing && (
                            <OpTag icon="🚜" label={`Ploughing (${methodLabel(recommended.method_ploughing)})`}/>
                        )}
                        {recommended.ridging && (
                            <OpTag icon="🔧" label={`Ridging (${methodLabel(recommended.method_ridging)})`}/>
                        )}
                        {!recommended.ploughing && !recommended.ridging && <OpTag icon="✋" label="No land preparation"/>}
                    </div>
                    <div className="d-flex flex-wrap gap-x-4 column-gap-4 row-gap-1 small">
                        <span>Cost: <strong>{fmt(recommended.TC, country)}</strong></span>
                        <span>Net revenue: <strong className={changeClass(recommended.NR >= 0)}>{fmt(recommended.NR, country)}</strong></span>
                        {recommended.dNR !== 0 && (
                            <span>Change vs current: <strong className={changeClass(recommended.dNR >= 0)}>
                                {signed(recommended.dNR, fmt(recommended.dNR, country))}
                            </strong></span>
                        )}
                    </div>
                </BestCard>
            )}

            {rows.length > 1 && (
                <Section title="All options compared">
                    <div className="table-responsive">
                        <table className="table table-sm align-middle mb-0">
                            <thead>
                            <tr>
                                <th scope="col">Operations</th>
                                <th scope="col">Method</th>
                                <th scope="col">Cost</th>
                                <th scope="col">Net Revenue</th>
                                <th scope="col">Change</th>
                                <th scope="col"><span className="visually-hidden">Marker</span></th>
                            </tr>
                            </thead>
                            <tbody>
                            {rows.map((r, i) => (
                                <tr key={i} className={r.CP ? 'table-active' : ''}>
                                    <td>
                                        <span className="d-flex flex-wrap gap-1">
                                            {r.ploughing && <span className="badge text-bg-secondary">Plough</span>}
                                            {r.ridging && <span className="badge text-bg-secondary">Ridge</span>}
                                            {!r.ploughing && !r.ridging && <span className="badge bg-body-secondary text-body border">None</span>}
                                        </span>
                                    </td>
                                    <td className="small text-nowrap">
                                        {r.ploughing ? methodLabel(r.method_ploughing) : '—'}&nbsp;/&nbsp;
                                        {r.ridging ? methodLabel(r.method_ridging) : '—'}
                                    </td>
                                    <td>{fmt(r.TC, country)}</td>
                                    <td className={changeClass(r.NR >= 0)}>{fmt(r.NR, country)}</td>
                                    <td className={changeClass(r.dNR >= 0)}>{signed(r.dNR, fmt(r.dNR, country))}</td>
                                    <td>
                                        {r.CP && <span className="badge text-bg-info">Your current</span>}
                                        {!r.CP && i === recommendedIndex && <span className="badge text-bg-success">Recommended</span>}
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                </Section>
            )}

            {current && (
                <Footnote>
                    Current practice: {current.ploughing ? `ploughing (${methodLabel(current.method_ploughing)})` : 'no ploughing'}
                    {current.ridging ? `, ridging (${methodLabel(current.method_ridging)})` : ', no ridging'}
                </Footnote>
            )}
        </div>
    )
}

function OpTag({icon, label}: Readonly<{icon: string; label: string}>) {
    return <span className="badge bg-body-secondary text-body border fs-6 fw-normal">{icon} {label}</span>
}
