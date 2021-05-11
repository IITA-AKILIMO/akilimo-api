package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.databind.JsonNode
import java.time.LocalDateTime


class PayloadDto {
    var id: Long = 0

    var requestId: String? = null

    var droidRequest: JsonNode? = null

    var plumberRequest: JsonNode? = null

    var plumberResponse: JsonNode? = null


    var createdAt: LocalDateTime? = null
    var updatedAt: LocalDateTime? = null
}
