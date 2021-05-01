package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.UserFeedback
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.data.repository.PagingAndSortingRepository
import org.springframework.stereotype.Repository

@Repository
interface UserFeedBackRepo : PagingAndSortingRepository<UserFeedback, Long> {

    override fun findAll(): List<UserFeedback>

    fun findAllByOrderByIdDesc(pageable: Pageable): Page<UserFeedback>

}
