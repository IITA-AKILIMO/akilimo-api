package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.AuthorityEntity
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface AuthorityRepo : JpaRepository<AuthorityEntity, Long>
