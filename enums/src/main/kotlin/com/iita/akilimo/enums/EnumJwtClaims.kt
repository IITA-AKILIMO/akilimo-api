package com.iita.akilimo.enums

enum class EnumJwtClaims(@Suppress("UNUSED_PARAMETER") name: String) {
    USERNAME("username"),
    EMAIL("email"),
    ID("id)");

    override fun toString(): String {
        return name
    }
}
