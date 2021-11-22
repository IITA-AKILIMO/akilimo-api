package com.iita.akilimo.database.entities

import com.fasterxml.jackson.annotation.JsonIgnore
import java.io.Serializable
import java.math.BigDecimal
import javax.persistence.*

@Entity
@Table(name = "fertilizers")
class FertilizerEntity : BaseEntity() {

    @Column(name = "name", nullable = false)
    var name: String? = null

    @Column(name = "type", nullable = false)
    var fertilizerType: String? = null


    @Column(name = "fertilizer_key")
    var fertilizerKey: Long? = null

    @Column(name = "weight", nullable = false)
    var weight: Int? = null

    @Column(name = "country", nullable = false)
    var country: String? = null


    @Column(name = "use_case", nullable = false)
    var useCase: String? = null

    @Column(name = "available")
    var available: Boolean? = null
}
