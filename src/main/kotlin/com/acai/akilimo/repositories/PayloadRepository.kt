package com.acai.akilimo.repositories

import com.acai.akilimo.entities.FertilizerPrices
import com.acai.akilimo.entities.OperationCost
import com.acai.akilimo.entities.Payload
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository
import java.sql.ClientInfoStatus

@Repository
interface PayloadRepository : JpaRepository<Payload, Long> {

    override fun findAll(): List<Payload>

    fun findByRequestId(requestId: Long): Payload

    fun findAllByRequestId(requestId: Long): List<Payload>
}
