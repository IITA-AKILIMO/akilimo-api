package com.iita.akilimo.database.entities

import com.iita.akilimo.database.entities.BaseEntity
import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table
import javax.validation.constraints.NotNull
import javax.validation.constraints.Size

@Entity
@Table(name = "users")
class UserEntity : BaseEntity() {

    @Size(max = 5)
    @NotNull
    @Column(name = "role", nullable = false, length = 5)
    var role: String? = null

    @Column(name = "username", nullable = false)
    var username: String? = null

    @Column(name = "password", nullable = false)
    var password: String? = null

    @Column(name = "enabled", nullable = false)
    var enabled: Boolean? = null
}
