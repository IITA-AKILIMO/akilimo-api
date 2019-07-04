package com.acai.akilimo.entities

import io.swagger.annotations.ApiModelProperty
import lombok.Data
import lombok.EqualsAndHashCode
import lombok.ToString
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

    @ApiModelProperty(example = "NPK151060", required = true)
    var fertilizerName: String? = null

    @Column(name = "N")
    @ApiModelProperty(example = "10", required = true)
    var nContent: Int? = null

    @Column(name = "P")
    @ApiModelProperty(example = "10", required = true)
    var pContent: Int? = null

    @Column(name = "K")
    @ApiModelProperty(example = "5", required = true)
    var kContent: Int? = null

    @ApiModelProperty(example = "50", required = true)
    var bagWeight: Int = 50

    var costPerBag: String? = "50"

    @Column(name = "active")
    @ApiModelProperty(example = "false", required = true)
    var isActive: Boolean = false

    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null


    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null

}