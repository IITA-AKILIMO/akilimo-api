package com.iita.akilimo.database.entities

import com.iita.akilimo.database.entities.BaseEntity
import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "users")
class UserEntity : BaseEntity() {
    @Column(name = "username", nullable = false)
    var username: String? = null

    @Column(name = "password", nullable = false)
    var password: String? = null

    @Column(name = "enabled", nullable = false)
    var enabled: Boolean? = null
}
