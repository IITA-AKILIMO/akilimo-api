package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.AppReportEntity
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.data.repository.PagingAndSortingRepository

interface AppReportRepo : PagingAndSortingRepository<AppReportEntity, Long> {
    override fun findAll(pageable: Pageable): Page<AppReportEntity>
}
