package com.iita.akilimo.database.repos

import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.*

@MappedSuperclass
open class ProducePrice {

    @Id
    @Column(name = "id")
    @GeneratedValue(strategy = GenerationType.SEQUENCE, generator = "native")
    @GenericGenerator(name = "native", strategy = "native")
    open var priceId: Long? = null

    @Column(name = "country")
    open var country: String? = null

    @Column(name = "min_local_price", columnDefinition = "decimal", precision = 10, scale = 2)
    open var minLocalPrice: Double? = null

    @Column(name = "max_local_price", columnDefinition = "decimal", precision = 10, scale = 2)
    open var maxLocalPrice: Double? = null

    @Column(name = "min_usd", columnDefinition = "decimal", precision = 10, scale = 2)
    open var minUsd: Double? = null

    @Column(name = "max_usd", columnDefinition = "decimal", precision = 10, scale = 2)
    open var maxUsd: Double? = null

    @Column(name = "price_active")
    open var priceActive: Boolean = false

    @Column(name = "min_price")
    open var minPrice: Boolean = false

    @Column(name = "max_price")
    open var maxPrice: Boolean = false

    @Column(name = "sort_order")
    open var sortOrder: Int = 0


    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    open var createdAt: LocalDateTime? = null

    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    open var updatedAt: LocalDateTime? = null
}
