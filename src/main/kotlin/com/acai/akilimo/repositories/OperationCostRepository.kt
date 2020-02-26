package com.acai.akilimo.repositories

import com.acai.akilimo.entities.FertilizerPrices
import com.acai.akilimo.entities.OperationCost
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository
import java.sql.ClientInfoStatus

@Repository
interface OperationCostRepository : JpaRepository<OperationCost, Long> {

    fun findByActiveIsTrue(): List<OperationCost>

    fun findByActiveIsTrueAndOperationNameAndOperationType(opName: String, opType: String): List<OperationCost>

}
