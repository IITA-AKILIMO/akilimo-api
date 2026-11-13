interface BadgeProps {
    active: boolean
    activeLabel?: string
    inactiveLabel?: string
}

export default function Badge({
    active,
    activeLabel = 'Active',
    inactiveLabel = 'Inactive',
}: BadgeProps) {
    return (
        <span className={`badge ${active ? 'bg-success' : 'bg-secondary'}`}>
            {active ? activeLabel : inactiveLabel}
        </span>
    )
}
