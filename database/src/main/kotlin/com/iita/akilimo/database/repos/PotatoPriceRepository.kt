package com.iita.akilimo.database.repos

import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface PotatoPriceRepository : JpaRepository<PotatoPrices, Long> {

    fun findByPriceId(priceId: Long): PotatoPrices?

    override fun findAll(): List<PotatoPrices>

    fun findAllByPriceActiveIsFalse(): List<PotatoPrices>

    fun findByCountryAndPriceActiveIsTrueOrderByMinLocalPriceAsc(country: String): List<PotatoPrices>
    fun findByCountryAndPriceActiveIsTrueOrderByMinLocalPriceDesc(country: String): List<PotatoPrices>

    fun findByCountryAndPriceActiveIsTrueOrderBySortOrderAsc(country: String): List<PotatoPrices>
    fun findByCountryAndPriceActiveIsTrueOrderBySortOrderDesc(country: String): List<PotatoPrices>
}
