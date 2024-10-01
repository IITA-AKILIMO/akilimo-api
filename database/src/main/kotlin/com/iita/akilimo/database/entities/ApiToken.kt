package com.iita.akilimo.database.entities

import javax.persistence.*
import javax.validation.constraints.NotNull
import javax.validation.constraints.Size

@Entity
@Table(name = "api_tokens")
@AttributeOverrides(
    AttributeOverride(name = "createdAt", column = Column(name = "created_at"))
)
open class ApiToken : BaseEntity() {
    @Size(max = 255)
    @NotNull
    @Column(name = "token", nullable = false)
    var token: String? = null

    @NotNull
    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "user_id", nullable = false)
    var user: UserEntity? = null
}
