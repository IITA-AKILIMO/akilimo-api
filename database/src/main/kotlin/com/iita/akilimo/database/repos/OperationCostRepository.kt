package com.iita.akilimo.database.repos

import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface OperationCostRepository : JpaRepository<OperationCost, Long> {

    fun findByActiveIsTrue(): List<OperationCost>

    fun findByActiveIsTrueAndOperationNameAndOperationType(opName: String, opType: String): List<OperationCost>
    fun findByActiveIsTrueAndOperationNameAndOperationTypeOrderByMaxUsdDesc(opName: String, opType: String): List<OperationCost>
    fun findByActiveIsTrueAndOperationNameAndOperationTypeOrderByMaxUsdAsc(opName: String, opType: String): List<OperationCost>

}
