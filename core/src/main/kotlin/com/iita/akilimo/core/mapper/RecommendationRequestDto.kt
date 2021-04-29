package com.iita.akilimo.core.mapper

import com.iita.akilimo.database.repos.Fertilizer
import java.time.LocalDateTime


open class RecommendationRequestDto {

    var mapLat: Long? =null

    var mapLong:Long?=null

    var cassavaUnitWeight: Double? =null

    var cassavaUnitPrice: Double?=null

    var maxInvestment: Double? =null

    var fieldArea: Double? =null

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
