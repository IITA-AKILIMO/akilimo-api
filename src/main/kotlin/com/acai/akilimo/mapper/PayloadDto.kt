package com.acai.akilimo.mapper

import com.fasterxml.jackson.databind.JsonNode
import lombok.Data
import org.hibernate.annotations.Type
import java.time.LocalDateTime


@Data
class PayloadDto {
    var id: Long = 0

    var requestId: String? = null

    var droidRequest: JsonNode? = null

    var plumberRequest: JsonNode? = null

    var plumberResponse: JsonNode? = null


    var createdAt: LocalDateTime? = null
    var updatedAt: LocalDateTime? = null
}