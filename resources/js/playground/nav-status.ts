import {fetchHealth} from './api'

// The top bar's status dot: green when /health says healthy, red when not (or unreachable)
const dot = document.querySelector<HTMLElement>('[data-health-dot]')

if (dot) {
    const mark = (up: boolean) => {
        dot.classList.remove('text-bg-secondary')
        dot.classList.add(up ? 'text-bg-success' : 'text-bg-danger')
        dot.title = up ? 'All systems operational' : 'Some checks failed'
    }
    fetchHealth()
        .then((d) => mark(d.status === 'healthy'))
        .catch(() => mark(false))
}
