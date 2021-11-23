package com.iita.akilimo.database.entities

import com.iita.akilimo.database.entities.BaseEntity
import java.math.BigDecimal
import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "investment_amount")
class InvestmentAmount : BaseEntity() {
    @Column(name = "country")
    var country: String? = null

    @Column(name = "investment_amount", nullable = false, columnDefinition = "decimal", precision = 10, scale = 2)
    var investmentAmount: BigDecimal? = null

    @Column(name = "are_unit")
    var areaUnit: String? = null

    @Column(name = "price_active")
    var priceActive: Boolean? = null

    @Column(name = "sort_order", nullable = false)
    var sortOrder: Int? = null
}
