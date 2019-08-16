package com.acai.akilimo.config

import lombok.Data
import net.bytebuddy.implementation.bind.annotation.Super
import org.springframework.http.HttpStatus

import java.util.Arrays
import springfox.documentation.spring.web.readers.operation.ResponseMessagesReader.message


@Data
class ApiError {

    var code: Int = 0
    var status: HttpStatus? = null
    var message: String? = null
    var errors: List<String>? = null


    constructor(code: Int, message: String) : super() {
        this.code = code
        this.message = message
    }


    constructor(status: HttpStatus, message: String, errors: List<String>) : super() {
        this.status = status
        this.message = message
        this.errors = errors
    }

    constructor(status: HttpStatus, message: String, error: String) : super() {
        this.status = status
        this.message = message
        errors = listOf(error)
    }
}
