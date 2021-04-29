package com.iita.akilimo.enums

import com.fasterxml.jackson.annotation.JsonProperty

enum class EnumUserType {
    @JsonProperty("EA")
    EA,

    @JsonProperty("COMMUNITY_MEMBER")
    COMMUNITY_MEMBER,

    @JsonProperty("FARMER")
    FARMER,

    @JsonProperty("OTHER")
    OTHER
}
