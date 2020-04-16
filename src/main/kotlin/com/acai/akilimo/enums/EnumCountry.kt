package com.acai.akilimo.enums

import com.fasterxml.jackson.annotation.JsonProperty

enum class EnumCountry {
    @JsonProperty("ke")
    KE {
        override fun currency(): String {
            return "KES"
        }
    },

    @JsonProperty("tz")
    TZ {
        override fun currency(): String {
            return "TZS"
        }
    },
    @JsonProperty("ng")
    NG {
        override fun currency(): String {
            return "NGN"
        }
    },
    @JsonProperty("all")
    ALL {
        override fun currency(): String {
            return "USD"
        }
    };

    abstract fun currency(): String
}