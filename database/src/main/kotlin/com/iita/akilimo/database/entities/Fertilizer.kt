package com.iita.akilimo.database.entities

import com.fasterxml.jackson.annotation.JsonIgnore
import java.io.Serializable
import java.math.BigDecimal
import javax.persistence.*

@Entity
@Table(name = "request_fertilizer")
class Fertilizer : Serializable {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "fertilizer_id", nullable = false)
    var fertilizerId: Long? = null

    @Column(name = "fertilizer_type", nullable = false)
    var fertilizerType: String? = null

    @Column(name = "available")
    var available: Boolean? = null

    @Column(name = "price", nullable = false)
    var price: BigDecimal? = null

    @Column(name = "weight", nullable = false)
    var weight: BigDecimal? = null


    @ManyToOne(cascade = [CascadeType.ALL])
    @JoinColumn(name = "request_id", referencedColumnName = "id")
    @JsonIgnore
    var recommendation: Recommendation? = null
}
