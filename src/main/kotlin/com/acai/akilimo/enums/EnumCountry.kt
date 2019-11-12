package com.acai.akilimo.enums

enum class EnumCountry {
    KE {
        override fun currency(): String {
            return "KES"
        }
    },
    TZ {
        override fun currency(): String {
            return "TZS"
        }
    },
    NG {
        override fun currency(): String {
            return "NGN"
        }
    },
    ALL {
        override fun currency(): String {
            return "USD"
        }
    };

    abstract fun currency(): String
}