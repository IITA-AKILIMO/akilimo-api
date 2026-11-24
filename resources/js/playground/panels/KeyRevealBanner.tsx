import {useState} from 'react'
import Icon from '../components/Icon'
import type {ApiKey, NewKey} from '../types'

// The AKILIMO MCP server (akilimo-mcp-agent) that MCP keys are for
export const MCP_URL = 'https://akilimo-mcp.akilimo.org/mcp'
const MCP_USE = 'mcp:use'

export const isMcpKey = (k: ApiKey) => (k.abilities ?? []).length === 1 && k.abilities[0] === MCP_USE

function mcpConfig(key: string): string {
    return JSON.stringify({
        mcpServers: {'akilimo-1.0': {type: 'http', url: MCP_URL, headers: {Authorization: `Bearer ${key}`}}},
    }, null, 2)
}

/** Shows a new key once, with a copy button (and a client config for MCP keys). */
export default function KeyRevealBanner({apiKey, onDismiss}: Readonly<{apiKey: NewKey; onDismiss: () => void}>) {
    const [copied, setCopied] = useState(false)

    function copy() {
        navigator.clipboard.writeText(apiKey.key).then(() => {
            setCopied(true)
            setTimeout(() => setCopied(false), 2000)
        })
    }

    return (
        <div className="alert alert-warning" role="alert">
            <div className="d-flex align-items-center gap-2 mb-2">
                <Icon name="shield" size={16}/>
                <strong>Save your API key — it won't be shown again</strong>
            </div>
            <div className="input-group mb-2">
                <code className="form-control text-break user-select-all">{apiKey.key}</code>
                <button type="button" className="btn btn-outline-secondary d-inline-flex align-items-center gap-1"
                        onClick={copy} title="Copy to clipboard">
                    <Icon name={copied ? 'check' : 'copy'}/>
                    {copied ? 'Copied' : 'Copy'}
                </button>
            </div>
            {isMcpKey(apiKey) && (
                <div className="small mb-2">
                    <p className="mb-1">
                        Connect your MCP client to <code>{MCP_URL}</code> with this key as{' '}
                        <code>Authorization: Bearer &lt;key&gt;</code> or <code>X-API-Key</code>.
                        For Claude Desktop, add this to <code>claude_desktop_config.json</code>:
                    </p>
                    <pre className="bg-body-tertiary border rounded p-2 mb-0 pg-json">{mcpConfig(apiKey.key)}</pre>
                </div>
            )}
            <button type="button" className="btn btn-sm btn-warning" onClick={onDismiss}>I've saved it</button>
        </div>
    )
}
