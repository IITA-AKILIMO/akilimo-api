package com.acai.akilimo.entities

import io.swagger.annotations.ApiModelProperty
import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.UpdateTimestamp
import java.io.Serializable
import java.time.LocalDateTime
import javax.persistence.*

@Entity
@Table(name = "available_fertilizer")
class AvailableFertilizers : Serializable {

    @Id
    @GeneratedValue(strategy = GenerationType.SEQUENCE, generator = "native")
    @GenericGenerator(name = "native", strategy = "native")
    var id: Long? = null

    @Column(name = "name")
    @ApiModelProperty(example = "NPK151010", required = true)
    var fertilizerName: String? = null

    @Column(name = "n")
    @ApiModelProperty(example = "10", required = true)
    var nContent: Int? = null

    @Column(name = "p")
    @ApiModelProperty(example = "10", required = true)
    var pContent: Int? = null

    @Column(name = "k")
    @ApiModelProperty(example = "5", required = true)
    var kContent: Int? = null

    @Column(name = "weight")
    @ApiModelProperty(example = "50", required = true)
    var bagWeight: Int = 50

    @Column(name = "price")
    var costPerBag: String? = "50"

    @Column(name = "available")
    @ApiModelProperty(example = "false", required = true)
    var isAvailable: Boolean = false

    @Column(name = "custom")
    @ApiModelProperty(example = "false", required = true)
    var isCustom: Boolean = false

    @Column(name = "country")
    var countryCode: String? = "50"

    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null


    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null

}