package com.iita.akilimo.database.entities

import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.Type
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.*

@Entity(name = "Payload")
@Table(name = "request_response")
class Payload : BaseEntity() {

    var requestId: String? = null

    @Type(type = "text")
    @Column(name = "droid_request")
    var droidRequest: String? = null

    @Type(type = "text")
    @Column(name = "plumber_request")
    var plumberRequest: String? = null

    @Type(type = "text")
    @Column(name = "plumber_response")
    var plumberResponse: String? = null
}
