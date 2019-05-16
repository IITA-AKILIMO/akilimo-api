package com.acai.akilimo.mapper

import com.acai.akilimo.entities.Fertilizer
import lombok.Data
import java.time.LocalDateTime
import javax.persistence.Column

@Data
open class RecommendationRequestDto {

    var mapLat: Float? =null

    var mapLong:Float?=null

    var cassavaUnitWeight: Float? =null

    var cassavaUnitPrice: Float?=null

    var maxInvestment: Float? =null

    var fieldArea: Float? =null

    var plantingDate: LocalDateTime? = null

    var harvestDate: LocalDateTime? = null

    var country: String? = null

    var client: String? = null

    var areaUnits: String? = null

    var userName: String? = null

    var userPhoneCode: String? = null

    var userPhoneNumber: String? = null

    var cassavaPd: String? = null

    var fieldDescription: String? = null

    var userEmail: String? = null

    var fertilizers: Set<Fertilizer>? = null

}
