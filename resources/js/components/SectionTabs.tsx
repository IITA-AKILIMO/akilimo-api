import { Link } from '@inertiajs/react'
import { isActive } from './Sidebar'

interface Tab { label: string; href: string; also?: string[] }
interface TabGroup { title: string; tabs: Tab[] }

/**
 * Pages that belong together under one menu entry are shown as a tab bar above
 * the page instead of as a submenu in the sidebar. A page is in a group when its
 * path is one of the group's tab pages (or the `also` pages of one).
 */
export const TAB_GROUPS: TabGroup[] = [
    {
        title: 'Fertilizers',
        tabs: [
            { label: 'By country', href: '/admin/fertilizers', also: ['/admin/fertilizer-types'] },
            { label: 'Fertilizer types', href: '/admin/reference/fertilizer-types' },
            { label: 'Categories', href: '/admin/reference/fertilizer-categories' },
            { label: 'Use cases', href: '/admin/reference/fertilizer-use-cases' },
        ],
    },
    {
        title: 'Units of sale',
        tabs: [
            { label: 'Cassava units', href: '/admin/cassava-units' },
            { label: 'Maize units', href: '/admin/reference/maize-units' },
        ],
    },
]

const onTab = (path: string, t: Tab) => isActive(path, t.href) || (t.also ?? []).some((h) => isActive(path, h))

export function tabGroupFor(path: string): TabGroup | undefined {
    return TAB_GROUPS.find((g) => g.tabs.some((t) => onTab(path, t)))
}

export default function SectionTabs({ currentPath }: { currentPath: string }) {
    const group = tabGroupFor(currentPath)
    if (!group) return null

    return (
        <nav aria-label={`${group.title} pages`} className="mb-3">
            <ul className="nav nav-tabs">
                {group.tabs.map((t) => {
                    const active = onTab(currentPath, t)
                    return (
                        <li className="nav-item" key={t.href}>
                            <Link href={t.href} className={`nav-link${active ? ' active' : ''}`} aria-current={active ? 'page' : undefined}>
                                {t.label}
                            </Link>
                        </li>
                    )
                })}
            </ul>
        </nav>
    )
}
