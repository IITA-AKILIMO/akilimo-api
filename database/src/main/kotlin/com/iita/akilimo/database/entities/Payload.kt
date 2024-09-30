package com.iita.akilimo.database.entities

import org.hibernate.annotations.Type
import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table

@Entity()
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
