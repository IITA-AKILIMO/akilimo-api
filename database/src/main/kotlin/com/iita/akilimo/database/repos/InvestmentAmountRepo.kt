package com.iita.akilimo.database.repos

import org.springframework.data.jpa.repository.JpaRepository
import com.iita.akilimo.database.entities.FertilizerPriceEntity
import com.iita.akilimo.database.entities.FertilizerPrices
import com.iita.akilimo.database.entities.InvestmentAmount
import org.springframework.stereotype.Repository

@Repository
interface InvestmentAmountRepo : JpaRepository<InvestmentAmount, Long> {

    fun findAllByCountryAndPriceActiveIsTrueOrderBySortOrderDesc(countryCode: String): List<InvestmentAmount>

    fun findAllByCountryAndPriceActiveIsTrueOrderBySortOrderAsc(countryCode: String): List<InvestmentAmount>

}
