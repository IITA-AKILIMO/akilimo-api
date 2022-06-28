package com.iita.akilimo.core.exceptions

import com.fasterxml.jackson.annotation.JsonFormat
import org.springframework.http.HttpStatus
import java.time.LocalDateTime

class ErrorResponse(
    val status: HttpStatus,
    val message: String,
    var stackTrace: String? = null,
) {
    @JsonFormat(shape = JsonFormat.Shape.STRING, pattern = "yyyy-MM-dd HH:mm:ss")
    val timeStamp: LocalDateTime = LocalDateTime.now()
}
