package com.iita.akilimo.database.repos

import com.fasterxml.jackson.annotation.JsonIgnoreProperties
import io.swagger.annotations.ApiModelProperty
import org.hibernate.annotations.*
import org.springframework.lang.NonNull
import java.io.Serializable
import java.time.LocalDateTime
import javax.persistence.*
import javax.persistence.CascadeType
import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "yield_request")
@JsonIgnoreProperties(value = ["createdAt", "updatedAt"], allowGetters = true)
class Recommendation : Serializable {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY, generator = "snowflake")
    @GenericGenerator(name = "snowflake", strategy = "com.acai.akilimo.generators.RequestSequenceGenerator")
    var id: Long? = null

    @Column(columnDefinition = "decimal", precision = 12, scale = 8)
    var mapLat: Float? = null

    @Column(columnDefinition = "decimal", precision = 12, scale = 8)
    var mapLong: Float? = null

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    var cassavaUnitWeight: Float? = null

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    var cassavaUnitPrice: Float? = null

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    var maxInvestment: Float? = null

    @Column(columnDefinition = "decimal", precision = 10, scale = 2)
    var fieldArea: Float? = null

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

    @ApiModelProperty(example = "254", required = false)
    var userPhoneCode: String? = null

    @ApiModelProperty(example = "713456789", required = false)
    var userPhoneNumber: String? = null

    @ApiModelProperty(example = "\"roots\", \"chips\", \"flour\", \"gari\"", required = true)
    var cassavaPd: String? = null

    @ApiModelProperty(example = "Cassava field at the valley", required = true)
    var fieldDescription: String? = null

    @ApiModelProperty(example = "user@mail.com", required = false)
    var userEmail: String? = null

    @Column(name = "recommendation_text")
    var recommendationText: String? = null

    @Column(name = "processed")
    @ApiModelProperty(example = "false", required = true)
    var isProcessed: Boolean = false

    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null


    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null


    @OneToMany(
        fetch = FetchType.LAZY,
        targetEntity = Fertilizer::class,
        mappedBy = "recommendation",
        cascade = [CascadeType.ALL],
        orphanRemoval = true
    )
    @Fetch(FetchMode.JOIN)
    @NonNull
    var fertilizers: Set<Fertilizer>? = null

    fun addFertilizers(recommendation: Recommendation): Set<Fertilizer> {
        val fertilizerSet = HashSet<Fertilizer>()

        recommendation.fertilizers!!.forEach { requestFertilizer ->
            requestFertilizer.recommendation = recommendation
            fertilizerSet.add(requestFertilizer)
        }
        return fertilizerSet
    }
}
