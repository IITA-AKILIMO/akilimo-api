package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.FertilizerPrices
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface FertilizerPriceRepository : JpaRepository<FertilizerPrices, Long> {

    fun findByPriceActiveIsTrue(): List<FertilizerPrices>

    fun findByPriceActiveIsTrueOrderBySortOrderDesc(): List<FertilizerPrices>

    fun findByPriceActiveIsTrueOrderBySortOrderAsc(): List<FertilizerPrices>


    fun findBySortOrder(sortOrder: Int): FertilizerPrices
}
