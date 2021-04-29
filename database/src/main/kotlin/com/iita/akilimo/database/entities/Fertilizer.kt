package com.iita.akilimo.database.entities

import com.fasterxml.jackson.annotation.JsonIgnore
import io.swagger.annotations.ApiModelProperty
import org.hibernate.annotations.GenericGenerator
import java.io.Serializable
import javax.persistence.*

@Entity
@Table(name = "request_fertilizer")
class Fertilizer : Serializable {

    @Id
    @GeneratedValue(strategy = GenerationType.SEQUENCE, generator = "native")
    @GenericGenerator(name = "native", strategy = "native")
    var fertilizerId: Long ?= null


    @Column(name = "request_id", insertable = false, updatable = false)
    @JsonIgnore
    var requestId: Long? = null

    @ApiModelProperty(example = "urea", required = true)
    var fertilizerType: String? = null

    @Column(name = "available")
    @ApiModelProperty(example = "true", required = true)
    var isAvailable: Boolean = false

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    @ApiModelProperty(example = "10.50", required = true)
    var price: Float = 0.0F

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    @ApiModelProperty(example = "50", required = true)
    var weight: Float?= 0.0F


    @ManyToOne(cascade = [CascadeType.ALL])
    @JoinColumn(name = "request_id", referencedColumnName = "id")
    @JsonIgnore
    var recommendation: Recommendation? = null
}
