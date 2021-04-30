package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.annotation.JsonPropertyOrder
import com.iita.akilimo.enums.EnumRoles

@JsonPropertyOrder("id", "username", "authority")
class AuthorityDto : BaseDto() {
    var id: Long? = null
    var username: String? = null
    var authority: EnumRoles? = null
}
