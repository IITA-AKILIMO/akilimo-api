package com.iita.akilimo.core.response

import com.fasterxml.jackson.annotation.JsonFormat
import java.time.LocalDateTime

data class LoginResponse(
    val accessToken: String,
    val expiry: Long,
    val expiryDate: LocalDateTime
)
