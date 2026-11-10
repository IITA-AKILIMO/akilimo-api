import { Link } from '@inertiajs/react'
import AdminLayout from '../../layouts/AdminLayout'

interface Props {
    tables: { slug: string; title: string; description: string; rows: number }[]
}

export default function ReferenceHub({ tables }: Props) {
    return (
        <AdminLayout title="Price settings">
            <p className="text-body-secondary" style={{ maxWidth: 760 }}>
                The lists the price forms choose from, and the rules the app&apos;s price lists are worked out with. Prices
                themselves are set on <Link href="/admin/default-prices">Default prices</Link>; what the app is given is shown on{' '}
                <Link href="/admin/price-lists">App price lists</Link>.
            </p>
            <div className="row g-3">
                {tables.map((t) => (
                    <div className="col-md-6 col-xl-4" key={t.slug}>
                        <Link href={`/admin/reference/${t.slug}`} className="text-decoration-none">
                            <div className="card border-0 shadow-sm h-100">
                                <div className="card-body">
                                    <div className="d-flex justify-content-between align-items-start">
                                        <h6 className="fw-semibold mb-2 text-body">{t.title}</h6>
                                        <span className="badge bg-secondary">{t.rows}</span>
                                    </div>
                                    <p className="small text-body-secondary mb-0">{t.description}</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                ))}
            </div>
        </AdminLayout>
    )
}
