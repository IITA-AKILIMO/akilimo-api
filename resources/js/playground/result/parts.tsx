import type {ReactNode} from 'react'

// Small pieces the per-use-case results share. Colours are Bootstrap's contextual ones.

export type Tone = 'success' | 'danger' | 'warning' | 'primary' | 'secondary' | 'info'

/** Text colour for a gain (green) or loss (red). */
export const changeClass = (positive: boolean) => (positive ? 'text-success' : 'text-danger')

export function MetricCard({icon, label, value, sub, tone}: Readonly<{
    icon: string; label: string; value: string; sub: ReactNode; tone: Tone
}>) {
    return (
        <div className="col-6 col-lg-3">
            <div className={`card h-100 border-${tone}-subtle bg-${tone}-subtle`}>
                <div className="card-body p-3">
                    <div className="fs-4 mb-1" aria-hidden="true">{icon}</div>
                    <div className="small text-body-secondary">{label}</div>
                    <div className="fs-5 fw-bold">{value}</div>
                    <div className="small text-body-secondary">{sub}</div>
                </div>
            </div>
        </div>
    )
}

export function Section({title, children}: Readonly<{title: string; children: ReactNode}>) {
    return (
        <section className="mt-4">
            <h4 className="h6 text-uppercase text-body-secondary fw-bold small">{title}</h4>
            {children}
        </section>
    )
}

export function Footnote({children}: Readonly<{children: ReactNode}>) {
    return <p className="small text-body-secondary mt-3 mb-0">{children}</p>
}

/** The "best option" card the planting-practices and schedule results lead with. */
export function BestCard({label, tone = 'success', children}: Readonly<{label: string; tone?: Tone; children: ReactNode}>) {
    return (
        <div className={`card border-${tone}-subtle bg-${tone}-subtle`}>
            <div className="card-body">
                <div className="small text-uppercase fw-bold text-body-secondary mb-2">{label}</div>
                {children}
            </div>
        </div>
    )
}

/** Table cell text for a signed money change, green or red. */
export const signed = (n: number, text: string) => (n >= 0 ? '+' : '') + text
