package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.AvailableFertilizers
import com.iita.akilimo.database.entities.FertilizerEntity
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository
import java.util.*

@Repository
interface FertilizerRepo : JpaRepository<FertilizerEntity, Long> {

    override fun findById(priceId: Long): Optional<FertilizerEntity>

    fun findAllByAvailableIsTrue(): List<FertilizerEntity>

    fun findByAvailableIsTrueAndCountryInOrderByNameDesc(countryCode: Collection<String>): List<FertilizerEntity>

    fun findByAvailableIsTrueAndCountryInOrderBySortOrderAscNameAsc(countryCode: Collection<String>): List<FertilizerEntity>

    fun findByAvailableIsTrueAndCountryInAndUseCaseOrderBySortOrderAscNameAsc(
        countryCode: Collection<String>,
        useCase: String
    ): List<FertilizerEntity>

}
