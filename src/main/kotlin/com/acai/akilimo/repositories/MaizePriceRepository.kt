package com.acai.akilimo.repositories

import com.acai.akilimo.entities.CassavaPrices
import com.acai.akilimo.entities.MaizePrices
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface MaizePriceRepository : JpaRepository<MaizePrices, Long> {

    fun findByPriceId(priceId: Long): MaizePrices?

    override fun findAll(): List<MaizePrices>

    fun findAllByActiveIsFalse(): List<MaizePrices>

    fun findByCountryAndActiveIsTrueOrderByMinLocalPriceAsc(country: String): List<MaizePrices>
    fun findByCountryAndActiveIsTrueOrderByMinLocalPriceDesc(country: String): List<MaizePrices>

    fun findByCountryAndActiveIsTrueOrderBySortOrderAsc(country: String): List<MaizePrices>
    fun findByCountryAndActiveIsTrueOrderBySortOrderDesc(country: String): List<MaizePrices>
}
