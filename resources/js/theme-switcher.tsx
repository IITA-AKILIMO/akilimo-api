import { createRoot } from 'react-dom/client'
import ThemeSwitcher from './components/ThemeSwitcher'

// The playground's pages are Blade, not Inertia: mount the admin's theme
// switcher into any element marked for it, so both UIs share one control.
document.querySelectorAll<HTMLElement>('[data-theme-switcher]').forEach((el) => {
    createRoot(el).render(<ThemeSwitcher />)
})
