import { router, usePage } from '@inertiajs/react'
import Offcanvas from 'bootstrap/js/dist/offcanvas'
import { useEffect, useRef, useState, type ReactNode } from 'react'
import FlashBanner from '../components/FlashBanner'
import Sidebar, { findActive } from '../components/Sidebar'
import ThemeSwitcher from '../components/ThemeSwitcher'

interface AdminLayoutProps {
    title?: string
    children: ReactNode
}

const SIDEBAR_ID = 'admin-sidebar'
const COLLAPSED_KEY = 'admin-sidebar-collapsed'

function readCollapsed(): boolean {
    try {
        return localStorage.getItem(COLLAPSED_KEY) === '1'
    } catch {
        return false
    }
}

function HeaderIcon({ d }: { d: string }) {
    return (
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <path d={d} />
        </svg>
    )
}

export default function AdminLayout({ title, children }: AdminLayoutProps) {
    const { url } = usePage()
    const currentPath = url.split('?')[0]
    const active = findActive(currentPath)

    const [collapsed, setCollapsed] = useState(readCollapsed)
    const menuButton = useRef<HTMLButtonElement>(null)

    function toggleCollapsed() {
        setCollapsed((c) => {
            try {
                localStorage.setItem(COLLAPSED_KEY, c ? '0' : '1')
            } catch {
                // private mode: the choice just isn't remembered
            }
            return !c
        })
    }

    // Bootstrap's Offcanvas handles the drawer; we only close it on navigation
    // and hand focus back to the menu button when it's dismissed.
    useEffect(() => {
        const el = document.getElementById(SIDEBAR_ID)
        if (!el) return
        const drawer = Offcanvas.getOrCreateInstance(el)
        const onHidden = () => {
            if (!el.contains(document.activeElement) && document.activeElement !== document.body) return
            menuButton.current?.focus()
        }
        el.addEventListener('hidden.bs.offcanvas', onHidden)
        // 'start' fires before the page (and this layout) is replaced, so the
        // drawer's backdrop and scroll lock are cleaned up by Bootstrap itself.
        const offStart = router.on('start', () => drawer.hide())
        return () => {
            el.removeEventListener('hidden.bs.offcanvas', onHidden)
            offStart()
            // Disposing mid-transition would break Bootstrap's pending callbacks.
            if (!['show', 'showing', 'hiding'].some((c) => el.classList.contains(c))) drawer.dispose()
        }
    }, [])

    return (
        <div className={`admin-shell d-flex${collapsed ? ' is-collapsed' : ''}`}>
            <a href="#admin-main" className="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-primary z-3">
                Skip to content
            </a>

            <Sidebar id={SIDEBAR_ID} currentPath={currentPath} collapsed={collapsed} />

            <div className="admin-content d-flex flex-column flex-grow-1 overflow-hidden">
                <header className="admin-header d-flex align-items-center gap-2 border-bottom bg-body px-3 px-md-4">
                    <button
                        ref={menuButton}
                        type="button"
                        className="btn btn-outline-secondary border-0 header-icon-btn d-lg-none"
                        data-bs-toggle="offcanvas"
                        data-bs-target={`#${SIDEBAR_ID}`}
                        aria-controls={SIDEBAR_ID}
                        aria-label="Open menu"
                    >
                        <HeaderIcon d="M4 6h16M4 12h16M4 18h16" />
                    </button>
                    <button
                        type="button"
                        className="btn btn-outline-secondary border-0 header-icon-btn d-none d-lg-inline-flex"
                        onClick={toggleCollapsed}
                        aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
                        aria-pressed={collapsed}
                        title={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
                    >
                        <HeaderIcon d={collapsed ? 'M9 3v18M3 5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM14 9l3 3-3 3' : 'M9 3v18M3 5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM16 15l-3-3 3-3'} />
                    </button>

                    <span className="d-none d-sm-block d-lg-none">
                        <img src="/images/akilimo_logo_colored.png" alt="AKILIMO" className="header-logo logo-on-light" />
                        <img src="/images/akilimo_logo_white.png" alt="AKILIMO" className="header-logo logo-on-dark" />
                    </span>
                    <div className="vr my-3 d-none d-sm-block d-lg-none" aria-hidden="true" />

                    <div className="d-flex flex-column overflow-hidden lh-sm ms-1">
                        {active && active.section !== 'Overview' && (
                            <span className="page-eyebrow text-body-secondary">{active.section}</span>
                        )}
                        <h1 className="page-title h6 mb-0 text-truncate">{title ?? 'Dashboard'}</h1>
                    </div>

                    <div className="ms-auto flex-shrink-0">
                        <ThemeSwitcher />
                    </div>
                </header>

                <FlashBanner />

                <main id="admin-main" tabIndex={-1} className="admin-main flex-grow-1 overflow-auto p-3 p-md-4">
                    {children}
                </main>
            </div>
        </div>
    )
}
