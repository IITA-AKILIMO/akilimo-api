import {useState} from 'react'
import Icon from '../components/Icon'
import FertilizerResult from './FertilizerResult'
import PlantingPracticesResult from './PlantingPracticesResult'
import PlantingScheduleResult from './PlantingScheduleResult'
import type {Tone} from './parts'
import type {ApiResponse, FRData, PPRow, SPRow} from './types'

const REC_META: Record<string, {icon: string; label: string; tone: Tone}> = {
    FR: {icon: '🌱', label: 'Fertilizer Recommendation', tone: 'success'},
    IC: {icon: '🌽', label: 'Intercropping Recommendation', tone: 'warning'},
    PP: {icon: '🌿', label: 'Planting Practices', tone: 'primary'},
    SP: {icon: '📅', label: 'Planting Schedule', tone: 'info'},
}

/** The answer to one compute request: header, advice text, the use case's own display and the raw JSON. */
export default function RecommendationResult({result, country}: Readonly<{result: ApiResponse; country: string}>) {
    const {rec_type, recommendation, data, fertilizer_rates} = result.data
    const [showJson, setShowJson] = useState(false)
    const meta = REC_META[rec_type] ?? {icon: '📊', label: rec_type, tone: 'secondary' as Tone}

    return (
        <div className="card">
            <div className="card-body p-4">
                <div className="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <span className={`badge fs-6 text-bg-${meta.tone}`}>{meta.icon} {meta.label}</span>
                        <div className="small text-body-secondary mt-2">
                            {result.version && <>Engine v{result.version}<span className="mx-1">·</span></>}
                            {result.request_id && <>ID: <code>{result.request_id.slice(0, 8)}…</code></>}
                        </div>
                    </div>
                    <span className="badge text-bg-success">✓ Success</span>
                </div>

                {recommendation && (
                    <div className="alert alert-success d-flex gap-2" role="note">
                        <span aria-hidden="true">💬</span>
                        <div>
                            {recommendation.split('\n').filter(Boolean).map((line, i) => (
                                <p key={i} className="mb-1">{line}</p>
                            ))}
                        </div>
                    </div>
                )}

                {(rec_type === 'FR' || rec_type === 'IC') && !Array.isArray(data) && (
                    <FertilizerResult data={data as FRData} rates={fertilizer_rates} country={country}/>
                )}
                {rec_type === 'PP' && Array.isArray(data) && (
                    <PlantingPracticesResult rows={data as PPRow[]} country={country}/>
                )}
                {rec_type === 'SP' && Array.isArray(data) && (
                    <PlantingScheduleResult rows={data as SPRow[]} country={country}/>
                )}

                <div className="mt-4">
                    <button type="button" className="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2"
                            aria-expanded={showJson} onClick={() => setShowJson((v) => !v)}>
                        <Icon name={showJson ? 'up' : 'down'} size={13}/>
                        {showJson ? 'Hide' : 'View'} raw JSON response
                    </button>
                    {showJson && (
                        <pre className="bg-body-tertiary border rounded p-3 mt-3 mb-0 small pg-json">
                            {JSON.stringify(result, null, 2)}
                        </pre>
                    )}
                </div>
            </div>
        </div>
    )
}
