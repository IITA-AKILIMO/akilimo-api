package com.iita.akilimo.database.repos

import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.*

@Entity

@Table(name = "starch_factory")
class StarchFactory {

    @Id
    @Column(name = "id")
    @GeneratedValue(strategy = GenerationType.SEQUENCE, generator = "native")
    @GenericGenerator(name = "native", strategy = "native")
    var id: Long? = null

    @Column(name = "factory_name")
    var factoryName: String? = null

    @Column(name = "factory_label")
    var factoryLabel: String? = null

    @Column(name = "country")
    var country: String? = null

    @Column(name = "factory_active")
    var factoryActive: Boolean = false

    @Column(name = "sort_order")
    var sortOrder: Int = 0

    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null

    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null
}
