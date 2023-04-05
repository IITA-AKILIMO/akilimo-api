package com.iita.akilimo.core.request

import com.fasterxml.jackson.annotation.JsonProperty
import com.iita.akilimo.enums.EnumRoles

data class AuthorityRequest(
    val username: String, @JsonProperty("role") val authority: EnumRoles = EnumRoles.ROLE_USER
)
