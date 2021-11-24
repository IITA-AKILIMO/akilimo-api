package com.iita.akilimo.database.entities

import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.*

@Entity
@Table(name = "fertilizer_prices")
class FertilizerPrices : BaseEntity() {

    @Column(name = "min_usd", columnDefinition = "decimal", precision = 10, scale = 2)
    var minUsd: Double? = null

    @Column(name = "max_usd", columnDefinition = "decimal", precision = 10, scale = 2)
    var maxUsd: Double? = null

    @Column(name = "price_per_bag", columnDefinition = "decimal", precision = 10, scale = 2)
    var pricePerBag: Double? = null

    @Column(name = "price_active")
    var priceActive: Boolean = false


    @Column(name = "sort_order")
    var sortOrder: Int = 0

    @Column(name = "desc")
    var description: String? = null
}
