package com.iita.akilimo.database.repos

import com.acai.akilimo.entities.AvailableFertilizers
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface FertilizerRepository : JpaRepository<AvailableFertilizers, Long> {

    fun findByFertilizerId(priceId: Long): AvailableFertilizers?

    fun findAllByAvailableIsTrue(): List<AvailableFertilizers>

    fun findByAvailableIsTrueAndCountryInOrderByNameDesc(countryCode: Collection<String>): List<AvailableFertilizers>

    fun findByAvailableIsTrueAndCountryInOrderBySortOrderAscNameAsc(countryCode: Collection<String>): List<AvailableFertilizers>

    fun findByAvailableIsTrueAndCountryInAndUseCaseOrderBySortOrderAscNameAsc(countryCode: Collection<String>, useCase: String): List<AvailableFertilizers>

}
