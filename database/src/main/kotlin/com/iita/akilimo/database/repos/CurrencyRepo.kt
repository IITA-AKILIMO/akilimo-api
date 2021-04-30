package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.CurrencyEntity
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface CurrencyRepo : JpaRepository<CurrencyEntity, Long> {
    fun findByCurrencyCode(toCurrency: String): CurrencyEntity?
}
