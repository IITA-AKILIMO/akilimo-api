package com.iita.akilimo.database.entities

import com.fasterxml.jackson.annotation.JsonIgnoreProperties
import org.hibernate.annotations.Fetch
import org.hibernate.annotations.FetchMode
import org.springframework.lang.NonNull
import java.math.BigDecimal
import java.util.*
import javax.persistence.*
import kotlin.collections.HashSet

@Entity
@Table(name = "yield_request")
@JsonIgnoreProperties(value = ["createdAt", "updatedAt"], allowGetters = true)
class Recommendation : BaseEntity() {

    @Column(name = "map_lat", nullable = false)
    var mapLat: BigDecimal? = null

    @Column(name = "map_long", nullable = false)
    var mapLong: BigDecimal? = null

    @Column(name = "cassava_unit_weight")
    var cassavaUnitWeight: BigDecimal? = null

    @Column(name = "cassava_unit_price")
    var cassavaUnitPrice: BigDecimal? = null

    @Column(name = "max_investment")
    var maxInvestment: BigDecimal? = null

    @Column(name = "field_area")
    var fieldArea: BigDecimal? = null

    @Column(name = "planting_date", nullable = false)
    var plantingDate: Date? = null

    @Column(name = "harvest_date", nullable = false)
    var harvestDate: Date? = null

    @Column(name = "country", nullable = false)
    var country: String? = null

    @Column(name = "client")
    var client: String? = null

    @Column(name = "area_units")
    var areaUnits: String? = null

    @Column(name = "user_name")
    var userName: String? = null

    @Column(name = "user_phone_code")
    var userPhoneCode: String? = null

    @Column(name = "user_phone_number")
    var userPhoneNumber: String? = null

    @Column(name = "cassava_pd")
    var cassavaPd: String? = null

    @Column(name = "field_description")
    var fieldDescription: String? = null

    @Column(name = "user_email")
    var userEmail: String? = null

    @Column(name = "processed")
    var processed: Boolean? = null


    @Column(name = "recommendation_text")
    var recommendationText: String? = null


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
