package com.iita.akilimo.enums

import com.fasterxml.jackson.annotation.JsonProperty

enum class EnumCrops {
    @JsonProperty("maize")
    maize,

    @JsonProperty("sweetpotato")
    sweetpotato
}
