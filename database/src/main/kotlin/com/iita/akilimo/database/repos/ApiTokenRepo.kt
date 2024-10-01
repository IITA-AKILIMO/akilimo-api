package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.ApiToken
import com.iita.akilimo.database.entities.UserEntity
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository
import java.util.*

@Repository
interface ApiTokenRepo : JpaRepository<ApiToken, Long> {

    fun findByToken(token: String): Optional<ApiToken>

    fun findByUserName(userName: String): Optional<ApiToken>
    fun findByUserNameAndIpAddress(userName: String, ipAddress: String): Optional<ApiToken>
}
