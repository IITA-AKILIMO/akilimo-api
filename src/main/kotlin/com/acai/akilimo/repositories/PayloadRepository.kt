package com.acai.akilimo.repositories

import com.acai.akilimo.entities.Payload
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface PayloadRepository : JpaRepository<Payload, Long> {

    override fun findAll(): List<Payload>

    fun findByRequestId(requestId: String): Payload

    fun findAllByRequestId(requestId: Long): List<Payload>
}
