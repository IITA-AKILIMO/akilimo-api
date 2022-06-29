package com.iita.akilimo.database.repos;

import com.iita.akilimo.database.entities.UserAuthEntity
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository
import java.util.*

@Repository
interface UserAuthRepo : JpaRepository<UserAuthEntity, Long> {
    fun findByApiKey(apiKey: String): Optional<UserAuthEntity>

    fun findByApiKeyAndEnabledIsTrue(apiKey: String): Optional<UserAuthEntity>
}
