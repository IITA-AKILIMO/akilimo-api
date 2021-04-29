package com.iita.akilimo.database.repos

import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "potato_prices")
class PotatoPrices : ProducePrice()
