package com.iita.akilimo.database.entities

import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.*

@Entity
@Table(name = "maize_prices")
class MaizePrices : ProducePrice() {
    @Column(name = "produce_type")
    var produceType: String? = null
}
