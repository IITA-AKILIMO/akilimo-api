package com.iita.akilimo.database.entities

//import io.swagger.annotations.ApiModelProperty
import java.io.Serializable
import java.math.BigDecimal
import java.util.*
import javax.persistence.*

@Entity
@Table(name = "available_fertilizer")
class AvailableFertilizers : Serializable {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id", nullable = false)
    var fertilizerId: Long? = null


    @Column(name = "name", nullable = false)
    var name: String? = null

    @Column(name = "type", nullable = false)
    var type: String? = null

    @Column(name = "n_content", nullable = false)
    var nContent: Int? = null

    @Column(name = "p_content", nullable = false)
    var pContent: Int? = null

    @Column(name = "k_content", nullable = false)
    var kContent: Int? = null

    @Column(name = "weight", nullable = false)
    var weight: Int? = null

    @Column(name = "price", nullable = false)
    var price: BigDecimal? = null

    @Column(name = "country", nullable = false)
    var country: String? = null

    @Column(name = "use_case")
    var useCase: String? = null

    @Column(name = "available")
    var available: Boolean? = null

    @Column(name = "custom")
    var custom: Boolean? = null

    @Column(name = "sort_order")
    var sortOrder: Int? = null

    @Column(name = "created_at")
    var createdAt: Date? = null

    @Column(name = "updated_at")
    var updatedAt: Date? = null

}
