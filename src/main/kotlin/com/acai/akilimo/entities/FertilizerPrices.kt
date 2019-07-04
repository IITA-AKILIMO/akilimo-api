package com.acai.akilimo.entities

import lombok.Data
import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.*

@Entity
@Data
@Table(name = "fertilizer_prices")
class FertilizerPrices {

    @Id
    //@GeneratedValue(strategy = GenerationType.IDENTITY, generator = "snowflake")
    //@GenericGenerator(name = "snowflake", strategy = "com.acai.akilimo.generators.RequestSequenceGenerator")
    @GeneratedValue(strategy = GenerationType.SEQUENCE, generator = "native")
    @GenericGenerator(name = "native", strategy = "native")
    var id: Long? = null

    @Column(name = "min_usd", columnDefinition = "decimal", precision = 10, scale = 2)
    var minUsd: Double? = null

    @Column(name = "max_usd", columnDefinition = "decimal", precision = 10, scale = 2)
    var maxUsd: Double? = null

    @Column(name = "price_active")
    var active: Boolean = false

    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null


    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null
}