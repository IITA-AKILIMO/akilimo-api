import {useState} from 'react'
import Icon, {type IconName} from './components/Icon'
import {usePlaygroundForm} from './hooks/usePlaygroundForm'
import {needsFertilizers, totalSteps} from './lib/scenario'
import HistoryPanel from './panels/HistoryPanel'
import TokensPanel from './panels/TokensPanel'
import RecommendationResult from './result/RecommendationResult'
import Stepper from './Stepper'
import StepCropYield from './steps/StepCropYield'
import StepDates from './steps/StepDates'
import StepFarm from './steps/StepFarm'
import StepFertilizers from './steps/StepFertilizers'
import StepScenario from './steps/StepScenario'

type Tab = 'playground' | 'history' | 'tokens'

const TABS: {id: Tab; label: string; icon: IconName}[] = [
    {id: 'playground', label: 'Playground', icon: 'play'},
    {id: 'history', label: 'History', icon: 'clock'},
    {id: 'tokens', label: 'API Keys', icon: 'lock'},
]

export default function App() {
    const [activeTab, setActiveTab] = useState<Tab>('playground')

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
        <main className="container-xl py-4">
            <header className="mb-3">
                <h1 className="h3 fw-bold">Try the <span className="text-primary">recommendations API</span></h1>
                <p className="text-body-secondary mb-0">
                    Pick a scenario, describe a farm, and see the recommendations the AKILIMO API returns for it.
                </p>
            </header>

            <div className="alert alert-info d-flex align-items-center gap-2 py-2 small" role="note">
                <Icon name="info" size={15}/>
                Limited to 5 requests per minute. To call the API from your own app, create a key in the API Keys tab.
            </div>

            <ul className="nav nav-tabs mb-3" role="tablist">
                {TABS.map((t) => (
                    <li key={t.id} className="nav-item" role="presentation">
                        <button type="button" role="tab" aria-selected={activeTab === t.id}
                                className={`nav-link d-inline-flex align-items-center gap-2${activeTab === t.id ? ' active' : ''}`}
                                onClick={() => setActiveTab(t.id)}>
                            <Icon name={t.icon}/> {t.label}
                        </button>
                    </li>
                ))}
            </ul>

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
                        <div ref={resultRef} className="mt-4">
                            {apiError && <div className="alert alert-danger" role="alert">✕ {apiError}</div>}
                            {result && <RecommendationResult result={result} country={form.country}/>}

                            <div className="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" className="btn btn-outline-secondary" onClick={reset}>↺ Start over</button>
                                <button type="button" className="btn btn-primary" onClick={submit} disabled={submitting}>
                                    {submitting ? 'Sending…' : '↻ Run again'}
                                </button>
                            </div>
                        </div>
                    )}
                </>
            )}
        </main>
    )
}
