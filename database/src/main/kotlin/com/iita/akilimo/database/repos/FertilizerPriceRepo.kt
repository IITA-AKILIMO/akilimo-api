package com.iita.akilimo.database.repos

import org.springframework.data.jpa.repository.JpaRepository
import com.iita.akilimo.database.entities.FertilizerPriceEntity
import com.iita.akilimo.database.entities.FertilizerPrices
import org.springframework.stereotype.Repository

@Repository
interface FertilizerPriceRepo : JpaRepository<FertilizerPriceEntity, Long> {
    fun findByPriceActiveIsTrue(): List<FertilizerPriceEntity>

    fun findByPriceActiveIsTrueOrderBySortOrderDesc(): List<FertilizerPriceEntity>

    fun findByPriceActiveIsTrueOrderBySortOrderAsc(): List<FertilizerPriceEntity>

    fun findAllByFertilizerKeyAndPriceActiveIsTrueOrderBySortOrderDesc(fertilizerId: String): List<FertilizerPriceEntity>

    fun findAllByFertilizerKeyAndPriceActiveIsTrueOrderBySortOrderAsc(fertilizerId: String): List<FertilizerPriceEntity>

    fun findBySortOrder(sortOrder: Int): FertilizerPriceEntity
}
