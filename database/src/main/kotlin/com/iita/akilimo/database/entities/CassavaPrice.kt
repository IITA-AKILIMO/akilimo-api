package com.iita.akilimo.database.entities

import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "cassava_prices")
open class CassavaPrice : ProducePrice()
