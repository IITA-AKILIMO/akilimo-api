package com.acai.akilimo.repositories

import com.acai.akilimo.entities.FertilizerPrices
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface FertilizerPriceRepository : JpaRepository<FertilizerPrices, Long> {

    fun findByPriceId(priceId: Long): FertilizerPrices?

    fun findByActiveIsTrue(): List<FertilizerPrices>

    fun findByActiveIsTrueOrderBySortOrderDesc(): List<FertilizerPrices>

    fun findByActiveIsTrueOrderBySortOrderAsc(): List<FertilizerPrices>


    fun findBySortOrder(sortOrder: Int): FertilizerPrices
}
