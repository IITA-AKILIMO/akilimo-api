package com.iita.akilimo.database.repos

import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.Type
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.*

@Entity(name = "Payload")
@Table(name = "request_response")
//@TypeDef(name = "json", typeClass = JsonStringType::class)
class Payload {

    @Id
    @Column(name = "id")
    @GeneratedValue(strategy = GenerationType.SEQUENCE, generator = "native")
    @GenericGenerator(name = "native", strategy = "native")
    var id: Long? = null

    var requestId: String? = null

    @Type(type = "text")
    var droidRequest: String? = null

    @Type(type = "text")
    var plumberRequest: String? = null

    @Type(type = "text")
    var plumberResponse: String? = null

    @CreationTimestamp
    @Column(name = "created_at", updatable = false, nullable = false)
    var createdAt: LocalDateTime? = null

    @UpdateTimestamp
    @Column(name = "updated_at", insertable = false)
    var updatedAt: LocalDateTime? = null
}
