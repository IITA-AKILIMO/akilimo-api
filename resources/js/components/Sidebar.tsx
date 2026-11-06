import { Link, router, usePage } from '@inertiajs/react'
import 'bootstrap/js/dist/collapse'
import { useMemo, useState, type FocusEvent, type MouseEvent } from 'react'
import type { PageProps, UserRole } from '../types'

// ── Inline SVG icons (stroke-based, 24×24 viewBox) ───────────────────────────

function Icon({ paths }: { paths: string[] }) {
    return (
        <svg
            className="nav-icon flex-shrink-0"
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth={1.75}
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
        >
            {paths.map((d) => <path key={d} d={d} />)}
        </svg>
    )
}

const ICONS = {
    dashboard:    ['M3 3h7v9H3z', 'M14 3h7v5h-7z', 'M14 12h7v9h-7z', 'M3 16h7v5H3z'],
    users:        ['M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2', 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm6.5-2a4 4 0 1 1 0-7.75M23 21v-2a4 4 0 0 0-3-3.87'],
    key:          ['M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4'],
    activity:     ['M22 12h-4l-3 9L9 3l-3 9H2'],
    feedback:     ['M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z'],
    ban:          ['M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z', 'M4.93 4.93l14.14 14.14'],
    tag:          ['M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 0 1 0 2.828l-7 7a2 2 0 0 1-2.828 0l-7-7A2 2 0 0 1 3 12V7a4 4 0 0 1 4-4z'],
    cassava:      ['M2.27 21.7s9.87-3.5 12.73-6.36a4.5 4.5 0 0 0-6.36-6.37C5.77 11.84 2.27 21.7 2.27 21.7z', 'M8.64 14l-2.05-2.04M15.34 15l-2.46-2.46M22 9s-1.33-2-3.5-2C16.86 7 15 9 15 9s1.33 2 3.5 2S22 9 22 9zM15 2s-2 1.33-2 3.5S15 9 15 9s2-1.84 2-3.5C17 3.33 15 2 15 2z'],
    maize:        ['M2 22L16 8', 'M3.47 12.53 5 11l1.53 1.53a3.5 3.5 0 0 1 0 4.94L5 19l-1.53-1.53a3.5 3.5 0 0 1 0-4.94zM7.47 8.53 9 7l1.53 1.53a3.5 3.5 0 0 1 0 4.94L9 15l-1.53-1.53a3.5 3.5 0 0 1 0-4.94zM11.47 4.53 13 3l1.53 1.53a3.5 3.5 0 0 1 0 4.94L13 11l-1.53-1.53a3.5 3.5 0 0 1 0-4.94zM20 2h2v2a4 4 0 0 1-4 4h-2V6a4 4 0 0 1 4-4z'],
    potato:       ['M12 3c4.5 0 8 3 8 8s-3.5 10-9 10-7-3-7-7 3-11 8-11z', 'M10 9h.01M14 13h.01M9 15h.01'],
    beaker:       ['M9 3h6M10 3v6L4.5 18.5A2 2 0 0 0 6.2 21.5h11.6a2 2 0 0 0 1.7-3L14 9V3', 'M7 15h10'],
    dollar:       ['M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
    wrench:       ['M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z'],
    trending:     ['M22 7l-8.5 8.5-5-5L2 17', 'M16 7h6v6'],
    sack:         ['M7 4h10l-1 3c2 2 3 5 3 8a6 6 0 0 1-6 6h-2a6 6 0 0 1-6-6c0-3 1-6 3-8z', 'M8 7h8'],
    globe:        ['M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z', 'M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z'],
    sprout:       ['M12 22V12m0 0C12 7 9 4 4 4c0 5 3 8 8 8zm0 0c0-5 3-8 8-8 0 5-3 8-8 8'],
    coins:        ['M8 14a6 6 0 1 0 0-12 6 6 0 0 0 0 12z', 'M18.09 10.37A6 6 0 1 1 10.34 18M7 6h1v4M16.71 13.88l.7.71-2.82 2.82'],
    scale:        ['M16 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1zM2 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z', 'M7 21h10M12 3v18M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2'],
    factory:      ['M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z', 'M17 18h1M12 18h1M7 18h1'],
    translate:    ['M5 8l6 6M4 14l6-6 2-3M2 5h12M7 2h1M22 22l-5-10-5 10', 'M14 18h7'],
    search:       ['M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16z', 'M21 21l-4.35-4.35'],
    signOut:      ['M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75'],
} satisfies Record<string, string[]>

// ── Nav structure ─────────────────────────────────────────────────────────────
// Roles per item mirror routes/web.php: everything outside the `role:admin`
// group is open to partners too.

const BOTH: UserRole[] = ['admin', 'partner']
const ADMIN: UserRole[] = ['admin']

interface NavItem {
    label: string
    href: string
    icon: keyof typeof ICONS
    roles: UserRole[]
    /** Label shown to partners instead, when it differs. */
    partnerLabel?: string
}

interface NavSection {
    heading: string
    items: NavItem[]
}

export const navigation: NavSection[] = [
    {
        heading: 'Overview',
        items: [
            { label: 'Dashboard', href: '/admin', icon: 'dashboard', roles: BOTH },
        ],
    },
    {
        heading: 'Keys and access',
        items: [
            { label: 'API keys', partnerLabel: 'My API keys', href: '/admin/api-keys', icon: 'key', roles: BOTH },
            { label: 'Users', href: '/admin/users', icon: 'users', roles: ADMIN },
        ],
    },
    {
        heading: 'Monitoring',
        items: [
            { label: 'Request log', href: '/admin/requests', icon: 'activity', roles: BOTH },
            { label: 'User feedback', href: '/admin/feedback', icon: 'feedback', roles: BOTH },
            { label: 'Excluded traffic', href: '/admin/traffic-exclusions', icon: 'ban', roles: ADMIN },
        ],
    },
    {
        heading: 'Prices and costs',
        items: [
            { label: 'Fertilizer prices', href: '/admin/fertilizer-prices', icon: 'tag', roles: ADMIN },
            { label: 'Cassava prices', href: '/admin/cassava-prices', icon: 'cassava', roles: ADMIN },
            { label: 'Maize prices', href: '/admin/maize-prices', icon: 'maize', roles: ADMIN },
            { label: 'Potato prices', href: '/admin/potato-prices', icon: 'potato', roles: ADMIN },
            { label: 'Starch prices', href: '/admin/starch-prices', icon: 'beaker', roles: ADMIN },
            { label: 'Default prices', href: '/admin/default-prices', icon: 'dollar', roles: ADMIN },
            { label: 'Operation costs', href: '/admin/operation-costs', icon: 'wrench', roles: ADMIN },
            { label: 'Investment amounts', href: '/admin/investment-amounts', icon: 'trending', roles: ADMIN },
        ],
    },
    {
        heading: 'Reference data',
        items: [
            { label: 'Fertilizers', href: '/admin/fertilizers', icon: 'sack', roles: ADMIN },
            { label: 'Crops', href: '/admin/crops', icon: 'sprout', roles: ADMIN },
            { label: 'Countries', href: '/admin/countries', icon: 'globe', roles: ADMIN },
            { label: 'Currencies', href: '/admin/currencies', icon: 'coins', roles: ADMIN },
            { label: 'Cassava units', href: '/admin/cassava-units', icon: 'scale', roles: ADMIN },
            { label: 'Starch factories', href: '/admin/starch-factories', icon: 'factory', roles: ADMIN },
            { label: 'Translations', href: '/admin/translations', icon: 'translate', roles: ADMIN },
        ],
    },
]

export function isActive(currentPath: string, href: string): boolean {
    if (href === '/admin') return currentPath === '/admin' || currentPath === '/admin/'
    return currentPath === href || currentPath.startsWith(href + '/')
}

/** The section and item the current page belongs to, for the header eyebrow. */
export function findActive(currentPath: string): { section: string; label: string } | null {
    for (const section of navigation) {
        const item = section.items.find((i) => isActive(currentPath, i.href))
        if (item) return { section: section.heading, label: item.label }
    }
    return null
}

// Matches Bootstrap's lg breakpoint, where the offcanvas becomes a static sidebar.
export const DESKTOP_QUERY = '(min-width: 992px)'

const NAV_ID = 'admin-nav'

interface SidebarProps {
    id: string
    currentPath: string
    /** Desktop icon rail; below lg the offcanvas always shows labels. */
    collapsed: boolean
}

/**
 * Bootstrap `offcanvas-lg`: a static sidebar from lg up, an off-canvas drawer
 * below it (backdrop, focus trap, Escape and scroll lock come from Bootstrap).
 */
export default function Sidebar({ id, currentPath, collapsed }: SidebarProps) {
    const { auth } = usePage<PageProps>().props
    const role = auth.user?.role

    const [filter, setFilter] = useState('')
    const [tooltip, setTooltip] = useState<{ label: string; top: number } | null>(null)

    const sections = useMemo(() => {
        const q = filter.trim().toLowerCase()
        return navigation
            .map((s) => ({
                heading: s.heading,
                items: s.items
                    .filter((i) => role !== undefined && i.roles.includes(role))
                    .map((i) => ({ ...i, label: role === 'partner' && i.partnerLabel ? i.partnerLabel : i.label }))
                    .filter((i) => !q || i.label.toLowerCase().includes(q) || s.heading.toLowerCase().includes(q)),
            }))
            .filter((s) => s.items.length > 0)
    }, [role, filter])

    const filtering = filter.trim() !== ''

    // The accordion opens on the current page's section; the first collapsible one otherwise.
    const activeHeading = findActive(currentPath)?.section
    const openSlug = ((sections.find((s) => s.heading === activeHeading && s.items.length > 1)
        ?? sections.find((s) => s.items.length > 1))?.heading ?? '').replace(/\W+/g, '-').toLowerCase()

    const itemCount = navigation.reduce(
        (n, s) => n + s.items.filter((i) => role !== undefined && i.roles.includes(role)).length,
        0,
    )

    // Labels are hidden only in the desktop rail, so that's the only place tooltips are needed.
    function showTooltip(label: string, e: MouseEvent<HTMLElement> | FocusEvent<HTMLElement>) {
        if (!collapsed || !window.matchMedia(DESKTOP_QUERY).matches) return
        const r = e.currentTarget.getBoundingClientRect()
        setTooltip({ label, top: r.top + r.height / 2 })
    }

    const tooltipProps = (label: string) => ({
        onMouseEnter: (e: MouseEvent<HTMLElement>) => showTooltip(label, e),
        onMouseLeave: () => setTooltip(null),
        onFocus: (e: FocusEvent<HTMLElement>) => showTooltip(label, e),
        onBlur: () => setTooltip(null),
    })

    return (
        <aside
            id={id}
            className="admin-sidebar offcanvas-lg offcanvas-start"
            tabIndex={-1}
            aria-label="Admin menu"
        >
            {/* Brand */}
            <div className="sidebar-brand d-flex align-items-center gap-2 border-bottom">
                <Link href="/admin" className="brand-logo rounded focus-ring" aria-label="AKILIMO admin home">
                    <img className="brand-logo-full logo-on-light" src="/images/akilimo_logo_colored.png" alt="" />
                    <img className="brand-logo-full logo-on-dark" src="/images/akilimo_logo_white.png" alt="" />
                    <img className="brand-logo-icon" src="/images/akilimo_mark.png" alt="" />
                </Link>
                <span className="brand-label badge rounded-pill bg-primary-subtle text-primary-emphasis">Admin</span>
                <button
                    type="button"
                    className="btn-close ms-auto d-lg-none"
                    data-bs-dismiss="offcanvas"
                    data-bs-target={`#${id}`}
                    aria-label="Close menu"
                />
            </div>

            <div className="offcanvas-body d-flex flex-column p-0">
                {/* Quick filter, only worth it when the list is long */}
                {itemCount > 6 && (
                    <div className="nav-filter px-3 pt-3">
                        <div className="input-group input-group-sm">
                            <span className="input-group-text bg-body text-body-tertiary">
                                <Icon paths={ICONS.search} />
                            </span>
                            <input
                                type="search"
                                className="form-control border-start-0 ps-0"
                                value={filter}
                                onChange={(e) => setFilter(e.target.value)}
                                onKeyDown={(e) => { if (e.key === 'Escape' && filter) { e.stopPropagation(); setFilter('') } }}
                                placeholder="Find a page"
                                aria-label="Find a page in the menu"
                            />
                        </div>
                    </div>
                )}

                {/* Navigation */}
                {/* Sections are a Bootstrap accordion (data-bs-parent): opening one closes the
                    others. A filter shows every match, so it renders them all open instead. */}
                <nav
                    id={NAV_ID}
                    key={filtering ? 'filtered' : 'accordion'}
                    className="nav-scroll flex-grow-1 overflow-y-auto px-3 pb-3"
                    aria-label="Main navigation"
                    onScroll={() => setTooltip(null)}
                >
                    {sections.map((section) => {
                        const slug = section.heading.replace(/\W+/g, '-').toLowerCase()
                        const headingId = `nav-h-${slug}`
                        const listId = `nav-s-${slug}`
                        // A single-link section has nothing to fold away.
                        const collapsible = !filtering && section.items.length > 1
                        const open = !collapsible || slug === openSlug
                        return (
                            <div key={section.heading} className="nav-section">
                                <h2 className="nav-heading">
                                    {collapsible ? (
                                        <button
                                            id={headingId}
                                            type="button"
                                            className={`nav-heading-toggle${open ? '' : ' collapsed'}`}
                                            data-bs-toggle="collapse"
                                            data-bs-target={`#${listId}`}
                                            aria-expanded={open}
                                            aria-controls={listId}
                                        >
                                            {section.heading}
                                            <svg className="nav-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                                                <path d="M6 9l6 6 6-6" />
                                            </svg>
                                        </button>
                                    ) : (
                                        <span id={headingId}>{section.heading}</span>
                                    )}
                                </h2>
                                <div
                                    id={listId}
                                    className={collapsible ? `collapse${open ? ' show' : ''}` : undefined}
                                    data-bs-parent={collapsible ? `#${NAV_ID}` : undefined}
                                >
                                <ul className="nav nav-pills flex-column gap-1" aria-labelledby={headingId}>
                                    {section.items.map((item) => {
                                        const active = isActive(currentPath, item.href)
                                        return (
                                            <li key={item.href} className="nav-item">
                                                <Link
                                                    href={item.href}
                                                    className={`nav-link d-flex align-items-center gap-2${active ? ' active' : ''}`}
                                                    aria-current={active ? 'page' : undefined}
                                                    {...tooltipProps(item.label)}
                                                >
                                                    <Icon paths={ICONS[item.icon]} />
                                                    <span className="nav-label text-truncate">{item.label}</span>
                                                </Link>
                                            </li>
                                        )
                                    })}
                                </ul>
                                </div>
                            </div>
                        )
                    })}
                    {sections.length === 0 && (
                        <p className="small text-body-secondary px-2 py-3 mb-0" role="status">No page matches “{filter}”.</p>
                    )}
                </nav>

                {/* User footer */}
                <div className="sidebar-footer d-flex align-items-center gap-2 border-top p-3">
                    <div className="user-avatar rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" aria-hidden="true">
                        {auth.user?.name?.charAt(0).toUpperCase()}
                    </div>
                    <div className="user-info flex-grow-1 overflow-hidden lh-sm">
                        <div className="d-flex align-items-center gap-2">
                            <span className="fw-medium small text-truncate">{auth.user?.name}</span>
                            <span className={`badge rounded-pill ${role === 'admin' ? 'bg-primary-subtle text-primary-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'}`}>{role}</span>
                        </div>
                        <div className="small text-body-secondary text-truncate">{auth.user?.username}</div>
                    </div>
                    <button
                        type="button"
                        onClick={() => router.post('/admin/logout')}
                        className="btn btn-sm btn-outline-secondary border-0 flex-shrink-0"
                        aria-label="Sign out"
                        {...tooltipProps('Sign out')}
                    >
                        <Icon paths={ICONS.signOut} />
                    </button>
                </div>
            </div>

            {tooltip && (
                <div className="tooltip bs-tooltip-end show rail-tooltip" role="tooltip" style={{ top: tooltip.top }}>
                    <div className="tooltip-arrow" />
                    <div className="tooltip-inner">{tooltip.label}</div>
                </div>
            )}
        </aside>
    )
}
