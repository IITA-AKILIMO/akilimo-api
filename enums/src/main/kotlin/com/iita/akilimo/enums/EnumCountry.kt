package com.iita.akilimo.enums

import com.fasterxml.jackson.annotation.JsonProperty

enum class EnumCountry {
    @JsonProperty("KE")
    KE {
        override fun currency(): String {
            return "KES"
        }
    },

    @JsonProperty("TZ")
    TZ {
        override fun currency(): String {
            return "TZS"
        }
    },

    @JsonProperty("NG")
    NG {
        override fun currency(): String {
            return "NGN"
        }
    },
    @JsonProperty("RW")
    RW {
        override fun currency(): String {
            return "RWF"
        }
    },
    @JsonProperty("GH")
    GH {
        override fun currency(): String {
            return "GHS"
        }
    },


    @JsonProperty("ALL")
    ALL {
        override fun currency(): String {
            return "USD"
        }
    };

    abstract fun currency(): String
}
