package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.annotation.JsonPropertyOrder

@JsonPropertyOrder("id", "username", "enabled")
class UserDto : BaseDto() {
    var id: Long? = null
    var username: String? = null
    var enabled: Boolean? = null
}
