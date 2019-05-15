package com.acai.akilimo.entities

import com.fasterxml.jackson.annotation.JsonIgnoreProperties
import io.swagger.annotations.ApiModelProperty
import lombok.Data
import org.hibernate.annotations.*
import org.springframework.lang.NonNull

import javax.persistence.CascadeType
import javax.persistence.Entity
import javax.persistence.Table
import javax.persistence.*
import javax.validation.constraints.Email
import java.io.Serializable
import java.time.LocalDateTime
import java.util.HashSet

@Entity
@Data
@Table(name = "yield_request")
@JsonIgnoreProperties(value = ["createdAt", "updatedAt"], allowGetters = true)
class RecommendationRequest : Serializable {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY, generator = "snowflake")
    @GenericGenerator(name = "snowflake", strategy = "com.acai.akilimo.generators.RequestSequenceGenerator")
    var id: Long ?=null

    @Column(columnDefinition = "decimal", precision = 12, scale = 8)
    var mapLat: Long? =null

    @Column(columnDefinition = "decimal", precision = 12, scale = 8)
    var mapLong:Long?=null

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    var cassavaUnitWeight: Double? =null

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    var cassavaUnitPrice: Double?=null

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    var maxInvestment: Double? =null

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    var fieldArea: Double? =null

    var plantingDate: LocalDateTime? = null

    var harvestDate: LocalDateTime? = null

    @ApiModelProperty(example = "KE", required = true)
    var country: String? = null

    @ApiModelProperty(example = "droid", required = true)
    var client: String? = null

    @ApiModelProperty(example = "acres", required = true)
    var areaUnits: String? = null

    @ApiModelProperty(example = "sammy", required = true)
    var userName: String? = null

    @ApiModelProperty(example = "254", required = true)
    var userPhoneCode: String? = null

    @ApiModelProperty(example = "713456789", required = true)
    var userPhoneNumber: String? = null

    @ApiModelProperty(example = "\"roots\", \"chips\", \"flour\", \"gari\"", required = true)
    var cassavaPd: String? = null

    @ApiModelProperty(example = "Cassava field at the valley", required = true)
    var fieldDescription: String? = null

    @Email
    @ApiModelProperty(example = "user@mail.com", required = true)
    var userEmail: String? = null

    @Column(name = "processed")
    @ApiModelProperty(example = "false", required = true)
    var isProcessed: Boolean = false

    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null


    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null

    @OneToMany(fetch = FetchType.LAZY, targetEntity = Fertilizer::class, mappedBy = "recommendationRequest", cascade = [CascadeType.ALL], orphanRemoval = true)
    @Fetch(FetchMode.JOIN)
    @NonNull
    var fertilizers: Set<Fertilizer>? = null

    fun addFertilizers(recommendationRequest: RecommendationRequest): Set<Fertilizer> {
        val fertilizerSet = HashSet<Fertilizer>()

        recommendationRequest.fertilizers!!.forEach { requestFertilizer ->
            requestFertilizer.recommendationRequest = recommendationRequest
            fertilizerSet.add(requestFertilizer)
        }
        return fertilizerSet
    }
}
