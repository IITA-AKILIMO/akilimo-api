package com.iita.akilimo.database.repos

import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "maize_prices")
class MaizePrices : ProducePrice() {
    @Column(name = "produce_type")
    var produceType: String? = null
}
