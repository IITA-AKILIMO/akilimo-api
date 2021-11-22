package com.iita.akilimo.database.entities

import com.iita.akilimo.database.entities.BaseEntity
import java.math.BigDecimal
import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "fertilizer_price")
class FertilizerPriceEntity : BaseEntity() {
    @Column(name = "country")
    var country: String? = null

    @Column(name = "fertilizer_key")
    var fertilizerKey: String? = null

    @Column(name = "min_price", nullable = false, columnDefinition = "decimal", precision = 10, scale = 2)
    var minPrice: BigDecimal? = null

    @Column(name = "max_price", nullable = false, columnDefinition = "decimal", precision = 10, scale = 2)
    var maxPrice: BigDecimal? = null

    @Column(name = "price_per_bag", nullable = false, columnDefinition = "decimal", precision = 10, scale = 2)
    var pricePerBag: BigDecimal? = null

    @Column(name = "price_active")
    var priceActive: Boolean? = null

    @Column(name = "sort_order", nullable = false)
    var sortOrder: Int? = null

    @Column(name = "desc")
    var desc: String? = null
}
