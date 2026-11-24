/** Bootstrap's spinner; the label is for screen readers (and shown when `text` is set). */
export default function Spinner({text}: Readonly<{text?: string}>) {
    return (
        <span className="d-inline-flex align-items-center gap-2">
            <span className="spinner-border spinner-border-sm" role="status" aria-hidden={text ? 'true' : undefined}>
                {!text && <span className="visually-hidden">Loading…</span>}
            </span>
            {text}
        </span>
    )
}
