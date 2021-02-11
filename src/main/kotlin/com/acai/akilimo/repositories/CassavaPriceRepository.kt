package com.acai.akilimo.repositories

import com.acai.akilimo.entities.CassavaPrices
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface CassavaPriceRepository : JpaRepository<CassavaPrices, Long> {

    fun findByPriceId(priceId: Long): CassavaPrices?

    override fun findAll(): List<CassavaPrices>

    fun findAllByActiveIsFalse(): List<CassavaPrices>

    fun findByCountryAndActiveIsTrueOrderByMinLocalPriceAsc(country: String): List<CassavaPrices>
    fun findByCountryAndActiveIsTrueOrderByMinLocalPriceDesc(country: String): List<CassavaPrices>

    fun findByCountryAndActiveIsTrueOrderBySortOrderAsc(country: String): List<CassavaPrices>
    fun findByCountryAndActiveIsTrueOrderBySortOrderDesc(country: String): List<CassavaPrices>

    fun findFirstByCountryAndMinLocalPriceGreaterThanOrderByMinLocalPriceAsc(countryCode: String, greater: Double): CassavaPrices

    fun findFirstByCountryAndMinPriceTrue(countryCode: String): CassavaPrices
    fun findFirstByCountryAndMaxPriceTrue(countryCode: String): CassavaPrices

    fun findFirstByCountryOrderByMinLocalPriceAsc(countryCode: String): CassavaPrices
    fun findFirstByCountryOrderByMaxLocalPriceDesc(countryCode: String): CassavaPrices
}
