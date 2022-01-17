package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.annotation.JsonPropertyOrder
import com.fasterxml.jackson.databind.JsonNode
import java.time.LocalDateTime

@JsonPropertyOrder("id", "requestId", "createdAt", "updatedAt")
class PayloadDto : BaseDto() {
    var id: Long? = null

    var requestId: String? = null

    var droidRequest: JsonNode? = null

    var plumberRequest: JsonNode? = null

    var plumberResponse: JsonNode? = null
}
