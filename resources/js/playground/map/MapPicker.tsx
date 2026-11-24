import {useEffect, useRef} from 'react'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix default marker icon paths broken by bundlers
delete (L.Icon.Default.prototype as unknown as Record<string, unknown>)._getIconUrl
L.Icon.Default.mergeOptions({
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
})

// ── Convex hull polygons derived from supported_coords.csv ────────────────────
// Coordinates are [lon, lat] (GeoJSON order)
const COUNTRY_HULLS: Record<string, [number, number][]> = {
    BI: [[29.025, -2.775], [29.325, -3.725], [29.475, -4.075], [29.525, -4.175], [29.575, -4.275], [29.675, -4.425], [29.725, -4.425], [29.925, -4.325], [30.025, -4.275], [30.175, -4.075], [30.775, -3.275], [30.825, -3.175], [30.825, -3.125], [30.525, -2.425], [30.475, -2.375], [29.975, -2.375], [29.075, -2.625]],
    GH: [[-3.075, 6.975], [-1.575, 5.075], [-1.425, 5.075], [-0.775, 5.275], [0.825, 5.775], [0.975, 5.875], [1.025, 5.975], [1.075, 6.075], [1.075, 6.175], [-0.225, 9.075], [-0.325, 9.175], [-1.175, 9.975], [-1.375, 10.075], [-1.625, 10.075], [-2.375, 9.775], [-2.725, 9.575]],
    NG: [[2.725, 6.475], [5.575, 5.075], [7.575, 4.525], [7.825, 4.525], [8.225, 4.575], [8.525, 4.725], [11.375, 6.525], [11.425, 6.575], [11.525, 6.675], [11.575, 6.725], [11.825, 7.075], [11.925, 7.425], [11.925, 9.025], [11.775, 9.275], [11.525, 9.575], [3.975, 10.125], [3.675, 10.125], [3.325, 9.775], [2.775, 9.025], [2.725, 8.425]],
    RW: [[28.875, -2.525], [28.925, -2.675], [29.025, -2.725], [29.375, -2.825], [29.525, -2.825], [29.825, -2.775], [30.775, -2.375], [30.825, -2.325], [30.875, -2.125], [30.875, -2.075], [30.825, -1.675], [30.725, -1.425], [30.475, -1.125], [30.425, -1.075], [30.375, -1.075], [29.525, -1.425], [29.375, -1.525], [29.275, -1.675]],
    TZ: [[29.625, -4.825], [29.775, -6.275], [29.875, -6.425], [38.175, -11.275], [38.375, -11.375], [38.525, -11.375], [39.225, -11.175], [39.825, -10.875], [40.025, -10.775], [40.375, -10.525], [40.375, -10.425], [39.875, -7.725], [39.525, -6.175], [39.175, -4.675], [39.125, -4.625], [35.125, -1.625], [34.875, -1.475], [34.425, -1.225], [34.325, -1.175], [34.125, -1.075], [31.825, -1.025], [30.725, -1.025], [30.475, -1.075], [29.625, -4.675]],
}

// Bounding boxes for fit-bounds on country select
const COUNTRY_BOUNDS: Record<string, L.LatLngBoundsExpression> = {
    BI: [[-4.425, 29.025], [-2.375, 30.825]],
    GH: [[5.075, -3.075], [10.075, 1.075]],
    NG: [[4.525, 2.725], [10.125, 11.925]],
    RW: [[-2.825, 28.875], [-1.075, 30.875]],
    TZ: [[-11.375, 29.625], [-1.025, 40.375]],
}

// Ray-casting point-in-polygon check. poly is [[lon, lat], ...]
function pointInPolygon(lon: number, lat: number, poly: [number, number][]): boolean {
    let inside = false
    for (let i = 0, j = poly.length - 1; i < poly.length; j = i++) {
        const [xi, yi] = poly[i]
        const [xj, yj] = poly[j]
        const intersect = ((yi > lat) !== (yj > lat)) &&
            (lon < (xj - xi) * (lat - yi) / (yj - yi) + xi)
        if (intersect) inside = !inside
    }
    return inside
}

function isSupported(lat: number, lon: number): boolean {
    return Object.values(COUNTRY_HULLS).some((hull) => pointInPolygon(lon, lat, hull))
}

interface Props {
    lat: string
    lng: string
    country: string
    onChange: (lat: number, lng: number) => void
}

export default function MapPicker({lat, lng, country, onChange}: Readonly<Props>) {
    const containerRef = useRef<HTMLDivElement>(null)
    const mapRef = useRef<L.Map | null>(null)
    const markerRef = useRef<L.Marker | null>(null)
    const flashRef = useRef<L.Circle | null>(null)

    // Initialize map once
    useEffect(() => {
        if (!containerRef.current || mapRef.current) return

        const map = L.map(containerRef.current, {
            center: [2, 22],
            zoom: 4,
            scrollWheelZoom: false,
            minZoom: 3,
        })

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18,
        }).addTo(map)

        // Draw supported-country overlays
        Object.entries(COUNTRY_HULLS).forEach(([code, hull]) => {
            // hull is [lon, lat] — Leaflet needs [lat, lon]
            const latlngs = hull.map(([lon, lat]) => [lat, lon] as [number, number])
            L.polygon(latlngs, {
                color: '#2d6a4f',
                weight: 1.5,
                opacity: 0.7,
                fillColor: '#52b788',
                fillOpacity: 0.15,
                className: 'map-country-polygon',
            }).bindTooltip(code, {permanent: false, direction: 'center', className: 'map-country-label'}).addTo(map)
        })

        map.on('click', (e: L.LeafletMouseEvent) => {
            const {lat: clickLat, lng: clickLng} = e.latlng

            if (!isSupported(clickLat, clickLng)) {
                // Flash red pulse to indicate unsupported area
                if (flashRef.current) flashRef.current.remove()
                flashRef.current = L.circle([clickLat, clickLng], {
                    radius: 40000, color: '#ef4444', fillColor: '#ef4444',
                    fillOpacity: 0.2, weight: 1.5, opacity: 0.6,
                }).addTo(map)
                setTimeout(() => {
                    flashRef.current?.remove();
                    flashRef.current = null
                }, 1200)
                return
            }

            onChange(Number.parseFloat(clickLat.toFixed(6)), Number.parseFloat(clickLng.toFixed(6)))
        })

        mapRef.current = map
        return () => {
            map.remove();
            mapRef.current = null
        }
    }, [])

    // Pan/zoom to selected country
    useEffect(() => {
        const map = mapRef.current
        if (!map) return
        const bounds = COUNTRY_BOUNDS[country]
        if (bounds) map.fitBounds(bounds, {padding: [24, 24], maxZoom: 8})
    }, [country])

    // Sync marker with lat/lng values
    useEffect(() => {
        const map = mapRef.current
        if (!map) return
        const latNum = Number.parseFloat(lat)
        const lngNum = Number.parseFloat(lng)
        if (Number.isNaN(latNum) || Number.isNaN(lngNum)) {
            markerRef.current?.remove()
            markerRef.current = null
            return
        }
        const pos: [number, number] = [latNum, lngNum]
        if (markerRef.current) {
            markerRef.current.setLatLng(pos)
        } else {
            markerRef.current = L.marker(pos, {draggable: true}).addTo(map)
            markerRef.current.on('dragend', () => {
                const {lat: dLat, lng: dLng} = markerRef.current!.getLatLng()
                if (!isSupported(dLat, dLng)) {
                    // Snap back to last valid position
                    markerRef.current!.setLatLng([Number.parseFloat(lat), Number.parseFloat(lng)])
                    return
                }
                onChange(Number.parseFloat(dLat.toFixed(6)), Number.parseFloat(dLng.toFixed(6)))
            })
        }
    }, [lat, lng])

    return (
        <div>
            <div ref={containerRef} className="pg-map rounded border"/>
            <p className="form-text mb-0">
                {lat && lng
                    ? <>📍 {Number.parseFloat(lat).toFixed(5)}, {Number.parseFloat(lng).toFixed(5)} — drag the pin or
                        click to reposition</>
                    : <>Click inside a highlighted country to pin your farm location</>
                }
            </p>
        </div>
    )
}
