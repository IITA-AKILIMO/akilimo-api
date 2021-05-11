package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.MaizePrice
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface MaizePriceRepository : JpaRepository<MaizePrice, Long> {

    fun findByPriceId(priceId: Long): MaizePrice?

    override fun findAll(): List<MaizePrice>

    fun findAllByPriceActiveIsFalse(): List<MaizePrice>

    fun findByCountryAndPriceActiveIsTrueOrderByMinLocalPriceAsc(country: String): List<MaizePrice>
    fun findByCountryAndPriceActiveIsTrueOrderByMinLocalPriceDesc(country: String): List<MaizePrice>

    fun findByCountryAndPriceActiveIsTrueOrderBySortOrderAsc(country: String): List<MaizePrice>
    fun findByCountryAndPriceActiveIsTrueOrderBySortOrderDesc(country: String): List<MaizePrice>

    fun findByCountryAndProduceTypeAndPriceActiveIsTrueOrderBySortOrderDesc(country: String, produceType: String): List<MaizePrice>
    fun findByCountryAndProduceTypeAndPriceActiveIsTrueOrderBySortOrderAsc(country: String, produceType: String): List<MaizePrice>
}
