package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.UserFeedback
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface UserFeedBackRepository : JpaRepository<UserFeedback, Long> {

    override fun findAll(): List<UserFeedback>

}