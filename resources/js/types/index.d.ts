export type UserRole = 'admin' | 'partner' | 'user'

export type PriceStatus = 'draft' | 'pending_second_review' | 'active'
export type PriceBasis = 'open_market' | 'subsidised'

export interface User {
    id: number
    name: string
    username: string
    email: string
    role: UserRole
    reviewer_countries?: string[]
    created_at?: string
}

export interface Country {
    currency_code?: string | null
    id: number
    code: string
    name: string
    active?: boolean
    default_price_basis?: PriceBasis
    sort_order?: number
    has_boundary?: boolean
}

export interface Fertilizer {
    id: number
    name: string
    type: string
    fertilizer_key: string
    fertilizer_label?: string
    category?: string
    country: string
    weight: number
    use_case: string
    available: boolean
    cis: boolean
    cim: boolean
    sort_order?: number
}

export interface FertilizerPrice {
    id: number
    country: string
    fertilizer_key: string
    min_price: number
    max_price: number
    price_per_bag: number
    price_active: boolean
    sort_order?: number
    desc?: string
}

export interface MaizePrice {
    id: number
    country: string
    produce_type: string
    min_price: number
    max_price: number
    min_local_price: number
    max_local_price: number
    min_usd: number
    max_usd: number
    price_active: boolean
    sort_order?: number
}

export interface CassavaPrice {
    id: number
    country: string
    min_price: number
    max_price: number
    min_local_price: number
    max_local_price: number
    min_usd: number
    max_usd: number
    price_active: boolean
    sort_order?: number
}

export interface PotatoPrice {
    id: number
    country: string
    min_price: number
    max_price: number
    min_local_price: number
    max_local_price: number
    min_usd: number
    max_usd: number
    price_active: boolean
    sort_order?: number
}

export interface StarchFactory {
    id: number
    factory_name: string
    factory_label: string
    country: string
    factory_active: boolean
    sort_order?: number
}

export interface StarchPrice {
    id: number
    starch_factory_id: number
    price_class: number
    min_starch: number
    range_starch?: string
    price: number
    currency?: string
}

export interface InvestmentAmount {
    id: number
    country: string
    investment_amount: number
    area_unit: string
    price_active: boolean
    sort_order?: number
}

export interface OperationCost {
    id: number
    operation_name: string
    operation_type: string
    country_code: string
    min_cost: number
    max_cost: number
    is_active: boolean
}

export interface Crop {
    id: number
    crop_key: string
    name: string
    description?: string | null
    active: boolean
    advice_available: boolean
    countries: string
    sort_order: number
}

export interface Currency {
    id: number
    country_code: string
    country: string
    currency_name: string
    currency_code: string
    currency_symbol: string
    currency_native_symbol?: string
    name_plural?: string
}

export interface CassavaUnit {
    id: number
    label: string
    unit_weight: number
    description?: string
    is_active: boolean
    sort_order?: number
}

export interface Translation {
    id: number
    key: string
    en: string
    sw?: string
    rw?: string
}

/** A field of a reference or settings table, as the generic Price settings screens describe it */
export interface ReferenceField {
    name: string
    label: string
    type: 'text' | 'number' | 'select'
    /** Part of the row's key: read-only once the row exists */
    key: boolean
    required: boolean
    help: string | null
    step: string | null
    default: number | null
}

/** One reference or settings table (ReferenceTables), without its validation rules */
export interface ReferenceResource {
    slug: string
    title: string
    description: string
    warning: string | null
    createUrl: string | null
    /** The table drives the app's price lists, so a change can be previewed before it is saved */
    canPreview: boolean
    fields: ReferenceField[]
}

/** What a change would do to one of the app's price lists (SettingsPreviewService) */
export interface PreviewList {
    key: string
    title: string
    total: number
    changed: number
    added: number
    removed: number
    truncated: boolean
    rows: { label: string; before: string | null; after: string | null }[]
}

export interface PreviewResult {
    changed: number
    lists: PreviewList[]
}

/** A row of such a table, with the key columns that address it */
export type ReferenceRow = Record<string, string | number | null | Record<string, string>> & { _key: Record<string, string> }

/** What a fertilizer is offered for (fertilizer_use_cases), picked in the fertilizer forms */
export interface FertilizerUseCase {
    value: string
    label: string
}

/** A fertilizer a country can sell (fertilizer_types), picked in the fertilizer forms */
export interface FertilizerTypeOption {
    type: string
    name: string
    weight: number
    category?: string
}

export interface PriceCurrency {
    code: string
    name: string
}

export interface PriceUnit {
    value: string
    label: string
}

export interface PriceItem {
    item: string
    /** The unit the item is priced in, so choosing the item fills in the unit */
    unit: string
}

/** The lists the price forms choose from (PriceLookupService::forForms), all read from the database */
export interface PriceLookups {
    currencies: PriceCurrency[]
    units: PriceUnit[]
    items: PriceItem[]
    /** The currency a country uses, so choosing the country fills in the currency */
    currencyByCountry: Record<string, string>
    /** The fertilizers a per-bag price can be for; the label is how the catalogue names it */
    fertilizerTypes: { type: string; name: string; label: string | null }[]
}

export interface DefaultPrice {
    category?: string
    id: number
    country: string
    region?: string | null
    item: string
    fertilizer_type?: string | null
    price: number
    subsidised_price?: number | null
    unit: string
    bag_kg?: number | null
    currency?: string
    min_price?: number | null
    max_price?: number | null
    status: PriceStatus
    source?: string | null
    source_date?: string | null
    confidence?: string | null
    effective_from?: string | null
    submitted_by?: number | null
    updated_at?: string
}

export interface DefaultPriceHistory {
    id: number
    action: string
    old_values?: Record<string, unknown> | null
    new_values?: Record<string, unknown> | null
    changed_by?: number | null
    note?: string | null
    created_at?: string
}

export interface AuthUser {
    id: number
    name: string
    username: string
    email: string
    role: UserRole
}

export interface Flash {
    success?: string | null
    error?: string | null
}

export interface PageProps {
    auth: {
        user: AuthUser | null
    }
    flash: Flash
    errors: Record<string, string>
    countries: Pick<Country, 'id' | 'code' | 'name'>[]
    [key: string]: unknown
}

export interface PaginationLinks {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
}

export interface PaginationMeta {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
}

export interface Paginated<T> {
    data: T[]
    links: PaginationLinks
    meta: PaginationMeta
}

export interface ApiRequest {
    id: number
    request_id: string
    device_token: string | null
    country_code: string | null
    full_names: string | null
    phone_number: string | null
    gender_code: string | null
    use_case: string | null
    excluded: boolean
    duration_ms: number | null
    created_at: string
}

export interface ApiRequestDetail extends ApiRequest {
    droid_request: Record<string, unknown>
    plumber_request: Record<string, unknown>
    plumber_response: Record<string, unknown>
}

export interface UserFeedback {
    id: number
    device_token: string | null
    use_case: string | null
    user_type: string
    akilimo_rec_rating: number
    akilimo_useful_rating: number
    language: string | null
    created_at: string
}

export interface ApiKey {
    id: number
    name: string
    key_prefix: string
    is_active: boolean
    abilities: string[] | null
    last_used_at: string | null
    expires_at: string | null
    created_at: string
    user: Pick<User, 'id' | 'name' | 'email'> | null
}
