package com.iita.akilimo.enums

import com.fasterxml.jackson.annotation.JsonProperty

enum class EnumRoles {
    @JsonProperty("ADMIN")
    ROLE_ADMIN,

    @JsonProperty("USER")
    ROLE_USER
}
