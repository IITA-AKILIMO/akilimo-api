package com.acai.akilimo.entities

import com.vladmihalcea.hibernate.type.json.JsonStringType
import org.hibernate.annotations.*
import java.time.LocalDateTime
import javax.persistence.*
import javax.persistence.Entity
import javax.persistence.Table

@Entity(name = "Payload")
@Table(name = "request_response")
@TypeDef(name = "json", typeClass = JsonStringType::class)
class Payload {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY, generator = "snowflake")
    @GenericGenerator(name = "snowflake", strategy = "com.acai.akilimo.generators.RequestSequenceGenerator")
    var id: Long = 0

    var requestId: String? = null

    @Type(type = "json")
    var droidRequest: String? = null

    @Type(type = "json")
    var plumberRequest: String? = null

    @Type(type = "json")
    var plumberResponse: String? = null

    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null

    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null
}