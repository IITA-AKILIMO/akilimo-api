package com.acai.akilimo.repositories

import com.acai.akilimo.entities.CurrencyEntity
import org.springframework.data.jpa.repository.JpaRepository

interface CurrencyRepo : JpaRepository<CurrencyEntity, Long> {
    fun findByCurrencyCode(toCurrency: String): CurrencyEntity?
}