package com.acai.akilimo.mapper

import com.acai.akilimo.entities.BaseEntity
import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table


class CurrencyDto {

    var country: String? = null

    var currencyName: String? = null

    var currencyCode: String? = null

    var currencySymbol: String? = null

    var currencyNativeSymbol: String? = null

    var namePlural: String? = null
}
