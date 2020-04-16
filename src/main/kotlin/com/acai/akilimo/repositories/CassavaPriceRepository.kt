package com.acai.akilimo.repositories

import com.acai.akilimo.entities.CassavaPrices
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface CassavaPriceRepository : JpaRepository<CassavaPrices, Long> {

    fun findByPriceId(priceId: Long): CassavaPrices?

    fun findByCountryAndActiveIsTrueOrderByMinLocalPriceDesc(country: String): List<CassavaPrices>
}
