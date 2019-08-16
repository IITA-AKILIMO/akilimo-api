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
    @Column(name = "id")
    @GeneratedValue(strategy = GenerationType.SEQUENCE, generator = "native")
    @GenericGenerator(name = "native", strategy = "native")
    var fertilizerId: Long? = null

    @Column(name = "name")
    @ApiModelProperty(example = "NPK 15:10:10", required = true)
    var name: String? = null

    @Column(name = "type")
    @ApiModelProperty(example = "NPK151010", required = true)
    var type: String? = null


    @ApiModelProperty(example = "10", required = true)
    var nContent: Int? = null

    @ApiModelProperty(example = "10", required = true)
    var pContent: Int? = null

    @ApiModelProperty(example = "5", required = true)
    var kContent: Int? = null

    @Column(name = "weight")
    @ApiModelProperty(example = "50", required = true)
    var weight: Int = 50

    @Column(name = "price")
    var price: String? = "50"

    @Column(name = "available")
    @ApiModelProperty(example = "false", required = true)
    var available: Boolean = false

    @Column(name = "custom")
    @ApiModelProperty(example = "false", required = true)
    var custom: Boolean = false

    @Column(name = "country")
    var country: String? = null

    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null


    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null

}