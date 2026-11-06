import {useState} from 'react'
import {usePlaygroundForm, needsFertilizers, totalSteps} from './usePlaygroundForm'
import Stepper from './Stepper'
import StepScenario from './StepScenario'
import StepFarm from './StepFarm'
import StepCropYield from './StepCropYield'
import StepFertilizers from './StepFertilizers'
import StepDates from './StepDates'
import RecommendationResult from './RecommendationResult'
import HistoryPanel from './HistoryPanel'
import TokensPanel from './TokensPanel'

export default function App() {
    const [activeTab, setActiveTab] = useState<'playground' | 'history' | 'tokens'>('playground')

    const {
        step, form, setForm,
        countries, cassavaUnits, priceHints, fertilizers, loadingFerts,
        submitting, result, apiError, validationErrors,
        resultRef,
        handleCountryChange, setCassavaUnit, setMaizeProduceType, updateFert,
        advance, back, submit, reset,
    } = usePlaygroundForm()

    const isLastStep = step === totalSteps(form.scenario)
    const datesStep  = needsFertilizers(form.scenario) ? 5 : 4

    return (
        <div className="pg-wrap">
            <div className="pg-header">
                <h1 className="pg-header-title">Try the <em>recommendations API</em></h1>
                <p className="pg-header-sub">
                    Pick a scenario, describe a farm, and see the recommendations the AKILIMO API
                    returns for it.
                </p>
            </div>

            <div className="pg-notice">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"
                     strokeLinecap="round" strokeLinejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Limited to 5 requests per minute. To call the API from your own app, create a key in the API Keys tab.
            </div>

            <div className="pg-tabs">
                <button className={`pg-tab${activeTab === 'playground' ? ' pg-tab--active' : ''}`}
                        onClick={() => setActiveTab('playground')}>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M5 3l14 9-14 9V3z"/>
                    </svg>
                    Playground
                </button>
                <button className={`pg-tab${activeTab === 'history' ? ' pg-tab--active' : ''}`}
                        onClick={() => setActiveTab('history')}>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    History
                </button>
                <button className={`pg-tab${activeTab === 'tokens' ? ' pg-tab--active' : ''}`}
                        onClick={() => setActiveTab('tokens')}>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    API Keys
                </button>
            </div>

            {activeTab === 'history' && <HistoryPanel/>}
            {activeTab === 'tokens' && <TokensPanel/>}

            {activeTab === 'playground' && (
                <>
                    <Stepper step={step} scenario={form.scenario}/>

                    {step === 1 && (
                        <StepScenario
                            scenario={form.scenario}
                            onSelect={(s) => setForm({scenario: s})}
                            onNext={advance}
                        />
                    )}

                    {step === 2 && (
                        <StepFarm
                            form={form}
                            countries={countries}
                            errors={validationErrors}
                            onCountryChange={handleCountryChange}
                            onChange={setForm}
                            onBack={back}
                            onNext={advance}
                        />
                    )}

                    {step === 3 && (
                        <StepCropYield
                            form={form}
                            cassavaUnits={cassavaUnits}
                            priceHints={priceHints}
                            errors={validationErrors}
                            isLastStep={isLastStep}
                            submitting={submitting}
                            onChange={setForm}
                            onCassavaUnitChange={setCassavaUnit}
                            onMaizeProduceTypeChange={setMaizeProduceType}
                            onBack={back}
                            onNext={advance}
                            onSubmit={submit}
                        />
                    )}

                    {step === 4 && needsFertilizers(form.scenario) && (
                        <StepFertilizers
                            country={form.country}
                            currency={priceHints.currency}
                            fertilizers={fertilizers}
                            loading={loadingFerts}
                            errors={validationErrors}
                            isLastStep={isLastStep}
                            submitting={submitting}
                            onUpdate={updateFert}
                            onBack={back}
                            onNext={advance}
                            onSubmit={submit}
                        />
                    )}

                    {step === datesStep && (
                        <StepDates
                            form={form}
                            errors={validationErrors}
                            submitting={submitting}
                            onChange={setForm}
                            onBack={back}
                            onSubmit={submit}
                        />
                    )}

                    {(result || apiError) && (
                        <div ref={resultRef}>
                            {apiError && <div className="pg-error">✕ {apiError}</div>}
                            {result && <RecommendationResult result={result} country={form.country}/>}

                            <div style={{marginTop: '1.5rem', display: 'flex', gap: '0.75rem', flexWrap: 'wrap'}}>
                                <button className="btn btn-outline" onClick={reset}>↺ Start over</button>
                                <button className="btn btn-terra" onClick={submit} disabled={submitting}>
                                    {submitting ? 'Sending…' : '↻ Run again'}
                                </button>
                            </div>
                        </div>
                    )}
                </>
            )}
        </div>
    )
}
