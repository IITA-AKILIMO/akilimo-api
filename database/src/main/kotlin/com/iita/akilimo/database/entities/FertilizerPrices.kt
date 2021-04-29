package com.iita.akilimo.database.repos

import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.*

@Entity
@Table(name = "fertilizer_prices")
class FertilizerPrices {

    @Id
    @Column(name = "id")
    //@GeneratedValue(strategy = GenerationType.IDENTITY, generator = "snowflake")
    //@GenericGenerator(name = "snowflake", strategy = "com.acai.akilimo.generators.RequestSequenceGenerator")
    @GeneratedValue(strategy = GenerationType.SEQUENCE, generator = "native")
    @GenericGenerator(name = "native", strategy = "native")
    var priceId: Long? = null

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

    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null


    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null
}
