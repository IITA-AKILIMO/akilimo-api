package com.iita.akilimo.database.entities

import com.acai.akilimo.entities.BaseEntity
import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "currencies")
class CurrencyEntity : BaseEntity() {
    @Column(name = "country")
    var country: String? = null

    @Column(name = "currency_name")
    var currencyName: String? = null

    @Column(name = "currency_code")
    var currencyCode: String? = null

    @Column(name = "currency_symbol")
    var currencySymbol: String? = null

    @Column(name = "currency_native_symbol")
    var currencyNativeSymbol: String? = null

    @Column(name = "name_plural")
    var namePlural: String? = null
}
