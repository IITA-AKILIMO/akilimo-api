// What POST /playground/compute returns: the compute service's recommendation envelope

export interface ApiResponse {
    request_id: string
    status: string
    version: string
    data: RecResult
}

export interface RecResult {
    rec_type: string
    recommendation: string
    data: FRData | PPRow[] | SPRow[] | unknown
    fertilizer_rates: FertilizerRate[]
}

export interface FRData {
    lat: number; lon: number; plDate: string
    N: number; P: number; K: number
    WLY: number; CurrentY: number; TargetY: number
    TC: number; NR: number
}

export interface PPRow {
    ploughing: boolean; ridging: boolean; harrowing?: boolean
    method_ploughing: string; method_ridging: string
    TC: number; NR: number; dNR: number; CP: boolean
}

export interface SPRow {
    PD: string; HD: string
    rPWnr: number; rHWnr: number
    GR: number; dGR: number; CP: boolean; rootUP: number
}

export interface FertilizerRate {
    type: string
    rate: number
}
