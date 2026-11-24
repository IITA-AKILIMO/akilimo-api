export interface PriceBand {
    min_local_price?: number | null
    max_local_price?: number | null
    average_price?: number | null
    price_per_bag?: number | null
}

/**
 * The middle band's price, skipping the 0 / -1 placeholder rows the lists use for
 * "exact price" and "I don't know"; null when the list has no real band.
 */
export function typicalPrice(bands: PriceBand[], field: 'average_price' | 'price_per_bag' = 'average_price'): number | null {
    const prices = bands
        .map((b) => Number(b[field] ?? ((Number(b.min_local_price) + Number(b.max_local_price)) / 2)))
        .filter((p) => Number.isFinite(p) && p > 0)
        .sort((a, b) => a - b)
    return prices.length ? prices[Math.floor(prices.length / 2)] : null
}

export function roundPrice(p: number): string {
    return String(p >= 100 ? Math.round(p) : Math.round(p * 100) / 100)
}
