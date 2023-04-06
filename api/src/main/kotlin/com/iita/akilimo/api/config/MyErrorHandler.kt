package com.iita.akilimo.api.config

import com.iita.akilimo.core.exceptions.AuthorizationException
import com.iita.akilimo.core.exceptions.ErrorResponse
import com.iita.akilimo.core.exceptions.NotFoundException
import org.hibernate.exception.SQLGrammarException
import org.springframework.dao.DataIntegrityViolationException
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.ControllerAdvice
import org.springframework.web.bind.annotation.ExceptionHandler
import java.sql.SQLException

@ControllerAdvice
class MyErrorHandler {
    @ExceptionHandler(
        NotFoundException::class
    )
    fun notFoundException(exc: RuntimeException): ResponseEntity<ErrorResponse> {
        val errorResponse = ErrorResponse(HttpStatus.NOT_FOUND, exc.message!!)
        return ResponseEntity(errorResponse, HttpStatus.NOT_FOUND)
    }

    @ExceptionHandler(
        AuthorizationException::class
    )
    fun forbiddenException(exc: RuntimeException): ResponseEntity<ErrorResponse> {
        val errorResponse = ErrorResponse(HttpStatus.UNAUTHORIZED, exc.message!!)
        return ResponseEntity(errorResponse, HttpStatus.UNAUTHORIZED)
    }

    @ExceptionHandler(
        DataIntegrityViolationException::class,
        SQLGrammarException::class,
        SQLException::class
    )
    fun sqlError(exc: RuntimeException): ResponseEntity<ErrorResponse> {
        val errorResponse = ErrorResponse(HttpStatus.INTERNAL_SERVER_ERROR, exc.message!!)
        return ResponseEntity(errorResponse, HttpStatus.INTERNAL_SERVER_ERROR)
    }

    @ExceptionHandler
    fun handleGenericException(exc: Exception): ResponseEntity<ErrorResponse> {
        val errorResponse = ErrorResponse(HttpStatus.BAD_REQUEST, exc.message!!)
        return ResponseEntity(errorResponse, HttpStatus.BAD_REQUEST)
    }

}
