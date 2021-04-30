package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.CassavaPrice
import org.springframework.data.repository.PagingAndSortingRepository
import org.springframework.stereotype.Repository

@Repository
interface CassavaPriceRepository : PagingAndSortingRepository<CassavaPrice, Long> {

    fun findByPriceId(priceId: Long): CassavaPrice?

    override fun findAll(): List<CassavaPrice>

    fun findAllByPriceActiveIsFalse(): List<CassavaPrice>

    fun findByCountryAndPriceActiveIsTrueOrderByMinLocalPriceAsc(country: String): List<CassavaPrice>
    fun findByCountryAndPriceActiveIsTrueOrderByMinLocalPriceDesc(country: String): List<CassavaPrice>

    fun findByCountryAndPriceActiveIsTrueOrderBySortOrderAsc(country: String): List<CassavaPrice>
    fun findByCountryAndPriceActiveIsTrueOrderBySortOrderDesc(country: String): List<CassavaPrice>

    fun findFirstByCountryAndMinLocalPriceGreaterThanOrderByMinLocalPriceAsc(countryCode: String, greater: Double): CassavaPrice

    fun findFirstByCountryAndMinPriceTrue(countryCode: String): CassavaPrice?
    fun findFirstByCountryAndMaxPriceTrue(countryCode: String): CassavaPrice?

    fun findFirstByCountryOrderByMinLocalPriceAsc(countryCode: String): CassavaPrice?
    fun findFirstByCountryOrderByMaxLocalPriceDesc(countryCode: String): CassavaPrice?
}
