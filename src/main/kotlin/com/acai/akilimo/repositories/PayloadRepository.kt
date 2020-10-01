package com.acai.akilimo.repositories

import com.acai.akilimo.entities.Payload
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.data.repository.PagingAndSortingRepository
import org.springframework.stereotype.Repository

@Repository
interface PayloadRepository : PagingAndSortingRepository<Payload, Long> {

    fun findByIdOrderByCreatedAtAsc(id: Long): Payload?

    override fun findAll(): List<Payload>

    override fun findAll(pageable: Pageable): Page<Payload>

    fun findAllByOrderByUpdatedAtDesc(pageable: Pageable): Page<Payload>

    fun findAllByRequestId(requestId: String): List<Payload>
}
