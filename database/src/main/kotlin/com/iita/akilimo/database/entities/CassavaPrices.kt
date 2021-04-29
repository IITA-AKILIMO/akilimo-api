package com.iita.akilimo.database.repos

import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "cassava_prices")
open class CassavaPrices : ProducePrice()
