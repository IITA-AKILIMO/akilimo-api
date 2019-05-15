package com.acai.akilimo.mapper

import lombok.Data

@Data
class RecommendationRequest {
    var areaUnits: String? = null
        set(areaUnits) {
            field = this.areaUnits
        }
    var area: Double = 0.toDouble()
        set(area) {
            field = this.area
        }
    var plantingDate: String? = null
        set(plantingDate) {
            field = this.plantingDate
        }
    var harvestDate: String? = null
        set(harvestDate) {
            field = this.harvestDate
        }
}
