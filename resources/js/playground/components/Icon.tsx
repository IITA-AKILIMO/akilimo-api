// The few line icons the playground uses, as one set so each is drawn once.

const PATHS = {
    play:     <path d="M5 3l14 9-14 9V3z"/>,
    clock:    <><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></>,
    lock:     <><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></>,
    info:     <><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></>,
    shield:   <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>,
    check:    <polyline points="20 6 9 17 4 12"/>,
    copy:     <><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></>,
    plus:     <><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></>,
    next:     <polyline points="9 18 15 12 9 6"/>,
    down:     <polyline points="6 9 12 15 18 9"/>,
    up:       <polyline points="18 15 12 9 6 15"/>,
    refresh:  <path d="M21 2v6h-6M3 12a9 9 0 0 1 15-6.7L21 8M3 22v-6h6M21 12a9 9 0 0 1-15 6.7L3 16"/>,
}

export type IconName = keyof typeof PATHS

export default function Icon({name, size = 14}: Readonly<{name: IconName; size?: number}>) {
    return (
        <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"
             strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" focusable="false" className="flex-shrink-0">
            {PATHS[name]}
        </svg>
    )
}
