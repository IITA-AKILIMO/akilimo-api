package com.iita.akilimo.core.request

class UserRequest(
    val username: String,
    val password: String,
    val enabled: Boolean = true
) {
}
