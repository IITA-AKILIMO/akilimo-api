package com.iita.akilimo.core.mapper

import com.fasterxml.jackson.annotation.JsonFormat
import java.time.LocalDateTime

open class BaseDto {
    @JsonFormat(shape = JsonFormat.Shape.STRING, pattern = "yyyy-MM-dd HH:mm:ss")
    var createdAt: String? = null

    @JsonFormat(shape = JsonFormat.Shape.STRING, pattern = "yyyy-MM-dd HH:mm:ss")
    var updatedAt: String? = null
}
