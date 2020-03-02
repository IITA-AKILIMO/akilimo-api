package com.acai.akilimo.mapper

import com.fasterxml.jackson.annotation.JsonIgnore
import com.fasterxml.jackson.annotation.JsonProperty
import lombok.Data
import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.Type
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.Column
import javax.persistence.GeneratedValue
import javax.persistence.GenerationType
import javax.persistence.Id


@Data
class PayloadDto {
    var id: Long = 0

    var requestId: Long = 0

    @Type(type = "json")
    var request: String? = null

    @Type(type = "json")
    var response: String? = null

    var createdAt: LocalDateTime? = null
    var updatedAt: LocalDateTime? = null
}