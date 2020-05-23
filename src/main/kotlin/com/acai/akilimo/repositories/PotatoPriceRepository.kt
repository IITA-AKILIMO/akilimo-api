package com.acai.akilimo.repositories

import com.acai.akilimo.entities.CassavaPrices
import com.acai.akilimo.entities.MaizePrices
import com.acai.akilimo.entities.PotatoPrices
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface PotatoPriceRepository : JpaRepository<PotatoPrices, Long> {

    fun findByPriceId(priceId: Long): PotatoPrices?

    override fun findAll(): List<PotatoPrices>

    fun findAllByActiveIsFalse(): List<PotatoPrices>

    fun findByCountryAndActiveIsTrueOrderByMinLocalPriceAsc(country: String): List<PotatoPrices>
    fun findByCountryAndActiveIsTrueOrderByMinLocalPriceDesc(country: String): List<PotatoPrices>

    fun findByCountryAndActiveIsTrueOrderBySortOrderAsc(country: String): List<PotatoPrices>
    fun findByCountryAndActiveIsTrueOrderBySortOrderDesc(country: String): List<PotatoPrices>
}
