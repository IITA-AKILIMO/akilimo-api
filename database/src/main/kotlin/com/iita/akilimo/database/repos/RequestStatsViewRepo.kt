package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.RequestStatsViewEntity
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.data.repository.PagingAndSortingRepository
import org.springframework.stereotype.Repository
import org.springframework.transaction.annotation.Transactional

@Repository
@Transactional
interface RequestStatsViewRepo : PagingAndSortingRepository<RequestStatsViewEntity, Long> {
    fun findAllByOrderByIdDesc(pageable: Pageable): Page<RequestStatsViewEntity>
}
