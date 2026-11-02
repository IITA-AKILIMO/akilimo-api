// Bootstrap 5 ships no type declarations; only the parts the admin layout uses.

// Imported for its data API (data-bs-toggle="collapse", data-bs-parent).
declare module 'bootstrap/js/dist/collapse'

declare module 'bootstrap/js/dist/offcanvas' {
    export default class Offcanvas {
        constructor(element: Element, options?: { backdrop?: boolean | 'static'; keyboard?: boolean; scroll?: boolean })
        static getInstance(element: Element): Offcanvas | null
        static getOrCreateInstance(element: Element): Offcanvas
        show(): void
        hide(): void
        toggle(): void
        dispose(): void
    }
}
