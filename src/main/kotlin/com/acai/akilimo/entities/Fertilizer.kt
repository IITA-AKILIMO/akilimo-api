package com.acai.akilimo.entities

import com.fasterxml.jackson.annotation.JsonIgnore
import io.swagger.annotations.ApiModelProperty
import lombok.Data
import lombok.EqualsAndHashCode
import lombok.ToString
import org.hibernate.annotations.GenericGenerator

import javax.persistence.*
import java.io.Serializable

@Data
@Entity
@ToString(exclude = ["yieldRequest"])
@EqualsAndHashCode(exclude = ["yieldRequest"])
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
    var price: Double = 0.0

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    @ApiModelProperty(example = "50", required = true)
    var weight: Double?= 0.0


    @ManyToOne(cascade = [CascadeType.ALL])
    @JoinColumn(name = "request_id", referencedColumnName = "id")
    @JsonIgnore
    var yieldRequest: YieldRequest? = null
}
