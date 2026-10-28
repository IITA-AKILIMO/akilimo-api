import { Link, router, usePage } from '@inertiajs/react'
import { useMemo, useState, type ReactNode } from 'react'
import type { PageProps, UserRole } from '../types'

// ── Inline SVG icons (stroke-based, 15×15 viewport) ──────────────────────────

function Icon({ d, d2 }: { d: string; d2?: string }) {
    return (
        <svg
            className="nav-icon"
            width="15"
            height="15"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth={1.75}
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
        >
            <path d={d} />
            {d2 && <path d={d2} />}
        </svg>
    )
}

const ICON_MAP: Record<string, ReactNode> = {
    Dashboard:            <Icon d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" d2="M9 22V12h6v10" />,
    Users:                <Icon d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" d2="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm6.5-2a4 4 0 1 1 0-7.75M23 21v-2a4 4 0 0 0-3-3.87" />,
    'API Keys':           <Icon d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4" />,
    'My API Keys':        <Icon d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4" />,
    Fertilizers:          <Icon d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18" />,
    'Fertilizer Prices':  <Icon d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 0 1 0 2.828l-7 7a2 2 0 0 1-2.828 0l-7-7A2 2 0 0 1 3 12V7a4 4 0 0 1 4-4z" />,
    'Maize Prices':       <Icon d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm0-14a1 1 0 0 0-1 1v4l3 3a1 1 0 0 0 1.414-1.414L13 10.586V7a1 1 0 0 0-1-1z" />,
    'Cassava Prices':     <Icon d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm0-14a1 1 0 0 0-1 1v4l3 3a1 1 0 0 0 1.414-1.414L13 10.586V7a1 1 0 0 0-1-1z" />,
    'Potato Prices':      <Icon d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm0-14a1 1 0 0 0-1 1v4l3 3a1 1 0 0 0 1.414-1.414L13 10.586V7a1 1 0 0 0-1-1z" />,
    'Starch Prices':      <Icon d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm0-14a1 1 0 0 0-1 1v4l3 3a1 1 0 0 0 1.414-1.414L13 10.586V7a1 1 0 0 0-1-1z" />,
    'Default Prices':     <Icon d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />,
    'Investment Amounts': <Icon d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />,
    'Operation Costs':    <Icon d="M9 7H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2v-3" d2="M9 11l3 3L22 4" />,
    'Starch Factories':   <Icon d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />,
    Countries:            <Icon d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z" d2="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />,
    Crops:                <Icon d="M12 22V12m0 0C12 7 9 4 4 4c0 5 3 8 8 8zm0 0c0-5 3-8 8-8 0 5-3 8-8 8" />,
    Currencies:           <Icon d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />,
    'Cassava Units':      <Icon d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />,
    Translations:         <Icon d="M5 8l6 6M4 14l6-6 2-3M2 5h12M7 2h1M22 22l-5-10-5 10" d2="M14 18h7" />,
    'Request Log':        <Icon d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" d2="M13 3H11a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v0a2 2 0 0 0-2-2z" />,
    'User Feedback':      <Icon d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />,
}

function getIcon(label: string) {
    return ICON_MAP[label] ?? (
        <Icon d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M13 3H11a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v0a2 2 0 0 0-2-2z" />
    )
}

// ── Nav structure ─────────────────────────────────────────────────────────────

interface NavItem {
    label: string
    href: string
}

interface NavGroup {
    heading: string
    roles: UserRole[]
    items: NavItem[]
}

const navigation: NavGroup[] = [
    {
        heading: 'Overview',
        roles: ['admin', 'partner'],
        items: [
            { label: 'Dashboard', href: '/admin' },
        ],
    },
    {
        heading: 'Management',
        roles: ['admin'],
        items: [
            { label: 'Users', href: '/admin/users' },
            { label: 'API Keys', href: '/admin/api-keys' },
            { label: 'Fertilizers', href: '/admin/fertilizers' },
            { label: 'Fertilizer Prices', href: '/admin/fertilizer-prices' },
        ],
    },
    {
        heading: 'Commodity Prices',
        roles: ['admin'],
        items: [
            { label: 'Maize Prices', href: '/admin/maize-prices' },
            { label: 'Cassava Prices', href: '/admin/cassava-prices' },
            { label: 'Potato Prices', href: '/admin/potato-prices' },
            { label: 'Starch Prices', href: '/admin/starch-prices' },
            { label: 'Default Prices', href: '/admin/default-prices' },
        ],
    },
    {
        heading: 'Supporting Data',
        roles: ['admin'],
        items: [
            { label: 'Investment Amounts', href: '/admin/investment-amounts' },
            { label: 'Operation Costs', href: '/admin/operation-costs' },
            { label: 'Starch Factories', href: '/admin/starch-factories' },
            { label: 'Countries', href: '/admin/countries' },
            { label: 'Crops', href: '/admin/crops' },
            { label: 'Currencies', href: '/admin/currencies' },
            { label: 'Cassava Units', href: '/admin/cassava-units' },
            { label: 'Translations', href: '/admin/translations' },
        ],
    },
    {
        heading: 'API Keys',
        roles: ['partner'],
        items: [
            { label: 'My API Keys', href: '/admin/api-keys' },
        ],
    },
    {
        heading: 'Monitoring',
        roles: ['admin', 'partner'],
        items: [
            { label: 'Request Log', href: '/admin/requests' },
            { label: 'User Feedback', href: '/admin/feedback' },
        ],
    },
]

function isActive(currentPath: string, href: string): boolean {
    return currentPath === href || currentPath.startsWith(href + '/')
}

interface SidebarProps {
    currentPath: string
}

export default function Sidebar({ currentPath }: SidebarProps) {
    const { auth } = usePage<PageProps>().props
    const role = auth.user?.role

    const visibleGroups = useMemo(
        () => navigation.filter((g) => role !== undefined && g.roles.includes(role)),
        [role],
    )

    const initialOpen = Object.fromEntries(
        visibleGroups.map((g) => [
            g.heading,
            g.items.some((i) => isActive(currentPath, i.href)),
        ]),
    )
    const [open, setOpen] = useState<Record<string, boolean>>(initialOpen)

    function toggle(heading: string) {
        setOpen((prev) => ({ ...prev, [heading]: !prev[heading] }))
    }

    function handleLogout() {
        router.post('/admin/logout')
    }

    return (
        <aside className="admin-sidebar flex-shrink-0">
            {/* Brand */}
            <div className="sidebar-brand">
                <div className="brand-logo-wrap">
                    <img src="/images/akilimo_logo_white.png" alt="Akilimo" />
                </div>
                <span className="brand-label">Admin</span>
            </div>

            {/* Navigation */}
            <nav className="nav-scroll" aria-label="Main navigation">
                {visibleGroups.map((group) => {
                    const isOpen = open[group.heading] ?? false
                    const hasActive = group.items.some((i) => isActive(currentPath, i.href))

                    return (
                        <div key={group.heading} className="nav-group">
                            <button
                                type="button"
                                onClick={() => toggle(group.heading)}
                                className="nav-group-toggle"
                                aria-expanded={isOpen}
                            >
                                <span className={`nav-group-label${hasActive ? ' is-active' : ''}`}>
                                    {group.heading}
                                </span>
                                <svg
                                    className={`nav-chevron${isOpen ? ' is-open' : ''}`}
                                    width="11"
                                    height="11"
                                    viewBox="0 0 12 12"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <path d="M6 8L1 3h10L6 8z" />
                                </svg>
                            </button>

                            <div style={{
                                overflow: 'hidden',
                                maxHeight: isOpen ? '600px' : '0',
                                transition: 'max-height 0.25s ease',
                            }}>
                                {group.items.map((item) => (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        className={`nav-link${isActive(currentPath, item.href) ? ' active' : ''}`}
                                    >
                                        {getIcon(item.label)}
                                        {item.label}
                                    </Link>
                                ))}
                            </div>
                        </div>
                    )
                })}
            </nav>

            {/* User footer */}
            <div className="sidebar-footer">
                <div className="d-flex align-items-center gap-2">
                    <div className="user-avatar">
                        {auth.user?.name?.charAt(0).toUpperCase()}
                    </div>
                    <div className="flex-grow-1 overflow-hidden">
                        <div className="d-flex align-items-center gap-2">
                            <span className="user-name text-truncate">{auth.user?.name}</span>
                            <span
                                className={`badge ${role === 'admin' ? 'bg-success' : 'bg-secondary'}`}
                                style={{ fontSize: '0.6rem' }}
                            >
                                {role}
                            </span>
                        </div>
                        <div className="user-meta text-truncate">{auth.user?.username}</div>
                    </div>
                    <button
                        onClick={handleLogout}
                        className="signout-btn"
                        title="Sign out"
                        aria-label="Sign out"
                    >
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" strokeWidth={1.75} stroke="currentColor" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>
    )
}
