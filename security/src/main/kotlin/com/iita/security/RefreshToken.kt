package com.iita.security

import java.time.LocalDateTime

data class RefreshToken(
    val id: String, val username: String, val token: String, val expiryDate: LocalDateTime
)
