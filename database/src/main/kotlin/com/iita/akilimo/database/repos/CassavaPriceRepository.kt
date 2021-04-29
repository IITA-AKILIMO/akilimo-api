package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.CassavaPrices
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface CassavaPriceRepository : JpaRepository<CassavaPrices, Long> {

    fun findByPriceId(priceId: Long): CassavaPrices?

    override fun findAll(): List<CassavaPrices>

    fun findAllByPriceActiveIsFalse(): List<CassavaPrices>

    fun findByCountryAndPriceActiveIsTrueOrderByMinLocalPriceAsc(country: String): List<CassavaPrices>
    fun findByCountryAndPriceActiveIsTrueOrderByMinLocalPriceDesc(country: String): List<CassavaPrices>

    fun findByCountryAndPriceActiveIsTrueOrderBySortOrderAsc(country: String): List<CassavaPrices>
    fun findByCountryAndPriceActiveIsTrueOrderBySortOrderDesc(country: String): List<CassavaPrices>

    fun findFirstByCountryAndMinLocalPriceGreaterThanOrderByMinLocalPriceAsc(countryCode: String, greater: Double): CassavaPrices

    fun findFirstByCountryAndMinPriceTrue(countryCode: String): CassavaPrices?
    fun findFirstByCountryAndMaxPriceTrue(countryCode: String): CassavaPrices?

    fun findFirstByCountryOrderByMinLocalPriceAsc(countryCode: String): CassavaPrices?
    fun findFirstByCountryOrderByMaxLocalPriceDesc(countryCode: String): CassavaPrices?
}
