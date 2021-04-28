package com.acai.akilimo.enums

import com.fasterxml.jackson.annotation.JsonProperty

enum class EnumUserType {
    @JsonProperty("COMMUNITY_MEMBER")
    COMMUNITY_MEMBER,

    @JsonProperty("FARMER")
    FARMER,

    @JsonProperty("OTHER")
    OTHER
}
