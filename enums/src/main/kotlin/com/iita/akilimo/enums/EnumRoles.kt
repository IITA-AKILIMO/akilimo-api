package com.iita.akilimo.enums

import com.fasterxml.jackson.annotation.JsonProperty

enum class EnumRoles {
    @JsonProperty("ADMIN")
    ADMIN,

    @JsonProperty("USER")
    USER
}
