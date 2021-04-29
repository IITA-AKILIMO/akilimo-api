package com.iita.akilimo.database.repos

import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface FertilizerPriceRepository : JpaRepository<FertilizerPrices, Long> {

    fun findByPriceId(priceId: Long): FertilizerPrices?

    fun findByPriceActiveIsTrue(): List<FertilizerPrices>

    fun findByPriceActiveIsTrueOrderBySortOrderDesc(): List<FertilizerPrices>

    fun findByPriceActiveIsTrueOrderBySortOrderAsc(): List<FertilizerPrices>


    fun findBySortOrder(sortOrder: Int): FertilizerPrices
}
