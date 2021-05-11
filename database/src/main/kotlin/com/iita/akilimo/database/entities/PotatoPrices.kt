package com.iita.akilimo.database.entities

import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "potato_prices")
class PotatoPrices : ProducePrice()
