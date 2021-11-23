package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.database.entities.BaseEntity
import java.math.BigDecimal
import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table

class InvestmentAmountDto : BaseDto() {

    @JsonProperty("investmentId")
    var id: Long? = null


    @JsonProperty("country")
    var country: String? = null

    @JsonProperty("investmentAmount")
    var investmentAmount: BigDecimal? = null

    @JsonProperty("areaUnit")
    var areaUnit: String? = null

    @JsonProperty("priceActive")
    var priceActive: Boolean? = null

    @JsonProperty("sortOrder")
    var sortOrder: Int? = null
}
