package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.MaizePrices
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface MaizePriceRepository : JpaRepository<MaizePrices, Long> {

    fun findByPriceId(priceId: Long): MaizePrices?

    override fun findAll(): List<MaizePrices>

    fun findAllByPriceActiveIsFalse(): List<MaizePrices>

    fun findByCountryAndPriceActiveIsTrueOrderByMinLocalPriceAsc(country: String): List<MaizePrices>
    fun findByCountryAndPriceActiveIsTrueOrderByMinLocalPriceDesc(country: String): List<MaizePrices>

    fun findByCountryAndPriceActiveIsTrueOrderBySortOrderAsc(country: String): List<MaizePrices>
    fun findByCountryAndPriceActiveIsTrueOrderBySortOrderDesc(country: String): List<MaizePrices>

    fun findByCountryAndProduceTypeAndPriceActiveIsTrueOrderBySortOrderDesc(country: String, produceType: String): List<MaizePrices>
    fun findByCountryAndProduceTypeAndPriceActiveIsTrueOrderBySortOrderAsc(country: String, produceType: String): List<MaizePrices>
}
