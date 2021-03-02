package com.acai.akilimo.repositories

import com.acai.akilimo.entities.UserFeedback
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface UserFeedBackRepository : JpaRepository<UserFeedback, Long> {

    override fun findAll(): List<UserFeedback>

}
