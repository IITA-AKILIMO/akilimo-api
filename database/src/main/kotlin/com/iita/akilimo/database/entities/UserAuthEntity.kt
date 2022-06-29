package com.iita.akilimo.database.entities

import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "user_auth")
class UserAuthEntity : BaseEntity() {
    @Column(name = "user_id", nullable = false)
    var userId: Long? = null

    @Column(name = "api_key", nullable = false)
    var apiKey: String? = null

    @Column(name = "enabled", nullable = false)
    var enabled: Boolean? = null
}
