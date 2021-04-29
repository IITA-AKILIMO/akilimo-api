package com.iita.akilimo.database.repos

import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.*

@Entity
@Table(name = "operation_costs")
class OperationCost {

    @Id
    @Column(name = "id")
    @GeneratedValue(strategy = GenerationType.SEQUENCE, generator = "native")
    @GenericGenerator(name = "native", strategy = "native")
    var id: Long? = null

    @Column(name = "operation_name")
    var operationName: String? = null

    @Column(name = "operation_type")
    var operationType: String? = null

    @Column(name = "min_usd", columnDefinition = "decimal", precision = 10, scale = 2)
    var minUsd: Double? = null

    @Column(name = "max_usd", columnDefinition = "decimal", precision = 10, scale = 2)
    var maxUsd: Double? = null

    @Column(name = "min_tzs", columnDefinition = "decimal", precision = 10, scale = 2)
    var minTzs: Double? = null

    @Column(name = "max_tzs", columnDefinition = "decimal", precision = 10, scale = 2)
    var maxTzs: Double? = null

    @Column(name = "min_ngn", columnDefinition = "decimal", precision = 10, scale = 2)
    var minNgn: Double? = null

    @Column(name = "max_ngn", columnDefinition = "decimal", precision = 10, scale = 2)
    var maxNgn: Double? = null

    @Column(name = "active")
    var active: Boolean = false


    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null

    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null
}
