package com.iita.akilimo.database.entities

import java.time.LocalDateTime
import javax.persistence.*
import javax.validation.constraints.NotNull
import javax.validation.constraints.Size

@Entity
@Table(name = "api_tokens")
@AttributeOverrides(
    AttributeOverride(name = "createdAt", column = Column(name = "created_at"))
)
class ApiToken : BaseEntity() {
    @Size(max = 255)
    @NotNull
    @Column(name = "token", nullable = false)
    var token: String? = null

    @Column(name = "username", length = 50)
    var userName: String? = null

    @Column(name = "ip_address", length = 50)
    var ipAddress: String? = null

    @Column(name = "expiry_date")
    var expiryDate: LocalDateTime? = null
}
