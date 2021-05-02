package com.iita.akilimo.database.entities

import com.iita.akilimo.enums.EnumRoles
import javax.persistence.*

@Entity
@Table(name = "authorities")
class AuthorityEntity : BaseEntity() {
    @Column(name = "username", nullable = false)
    var username: String? = null

    /**
     * Role name
     */
    @Enumerated(EnumType.STRING)
    @Column(name = "authority", nullable = false, columnDefinition = "TEXT")
    var authority: EnumRoles? = null

}
