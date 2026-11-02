import { useEffect, useState } from 'react'

// Bootstrap 5.3 colour modes: `data-bs-theme` on <html>. admin.blade.php applies
// the saved choice before first paint; this keeps it in sync afterwards.

type ThemeChoice = 'light' | 'dark' | 'auto'

const THEME_KEY = 'admin-theme'
const DARK_QUERY = '(prefers-color-scheme: dark)'

function readChoice(): ThemeChoice {
    try {
        const t = localStorage.getItem(THEME_KEY)
        return t === 'light' || t === 'dark' ? t : 'auto'
    } catch {
        return 'auto'
    }
}

function apply(choice: ThemeChoice) {
    const dark = choice === 'dark' || (choice === 'auto' && window.matchMedia(DARK_QUERY).matches)
    document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light')
}

const OPTIONS: { value: ThemeChoice; label: string; paths: string[] }[] = [
    { value: 'light', label: 'Light theme', paths: ['M12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10z', 'M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42'] },
    { value: 'dark', label: 'Dark theme', paths: ['M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z'] },
    { value: 'auto', label: 'Match system theme', paths: ['M4 4h16a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z', 'M8 21h8M12 17v4'] },
]

export default function ThemeSwitcher() {
    const [choice, setChoice] = useState<ThemeChoice>(readChoice)

    useEffect(() => {
        apply(choice)
        if (choice !== 'auto') return
        const mq = window.matchMedia(DARK_QUERY)
        const onChange = () => apply('auto')
        mq.addEventListener('change', onChange)
        return () => mq.removeEventListener('change', onChange)
    }, [choice])

    function choose(value: ThemeChoice) {
        try {
            if (value === 'auto') localStorage.removeItem(THEME_KEY)
            else localStorage.setItem(THEME_KEY, value)
        } catch {
            // private mode: the choice lasts for this page only
        }
        setChoice(value)
    }

    return (
        <div className="btn-group btn-group-sm theme-switcher" role="group" aria-label="Colour theme">
            {OPTIONS.map((o) => (
                <button
                    key={o.value}
                    type="button"
                    className={`btn btn-outline-secondary${choice === o.value ? ' active' : ''}`}
                    aria-pressed={choice === o.value}
                    aria-label={o.label}
                    title={o.label}
                    onClick={() => choose(o.value)}
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                        {o.paths.map((d) => <path key={d} d={d} />)}
                    </svg>
                </button>
            ))}
        </div>
    )
}
