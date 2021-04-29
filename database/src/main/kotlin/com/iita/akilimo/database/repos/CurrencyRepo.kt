package com.iita.akilimo.database.repos

import org.springframework.data.jpa.repository.JpaRepository

interface CurrencyRepo : JpaRepository<CurrencyEntity, Long> {
    fun findByCurrencyCode(toCurrency: String): CurrencyEntity?
}
