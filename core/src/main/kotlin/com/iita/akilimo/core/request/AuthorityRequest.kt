package com.iita.akilimo.core.request

import com.iita.akilimo.enums.EnumRoles

class AuthorityRequest(
    val username: String,
    val authority: EnumRoles
) {
}
