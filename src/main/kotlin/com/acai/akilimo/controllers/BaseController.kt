package com.acai.akilimo.controllers

import com.acai.akilimo.config.ApiError
import org.springframework.http.HttpHeaders
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.MethodArgumentNotValidException
import org.springframework.web.bind.annotation.ExceptionHandler
import org.springframework.web.bind.annotation.ResponseStatus
import org.springframework.web.bind.annotation.RestControllerAdvice
import org.springframework.web.context.request.WebRequest
import org.springframework.web.servlet.NoHandlerFoundException
import org.springframework.web.servlet.mvc.method.annotation.ResponseEntityExceptionHandler
import java.util.*
import javax.servlet.http.HttpServletRequest
import javax.servlet.http.HttpServletResponse


@RestControllerAdvice
class BaseController : ResponseEntityExceptionHandler() {

    /*
    @ExceptionHandler(value = [NoHandlerFoundException::class])
    @ResponseStatus(code = HttpStatus.BAD_REQUEST)
    fun badRequest(e: Exception, request: HttpServletRequest, response: HttpServletResponse): ApiError {

        return ApiError(400, HttpStatus.BAD_REQUEST.reasonPhrase)
    }
*/
    override fun handleMethodArgumentNotValid(ex: MethodArgumentNotValidException, headers: HttpHeaders, status: HttpStatus, request: WebRequest): ResponseEntity<Any> {
        //return super.handleMethodArgumentNotValid(ex, headers, status, request);

        val errors = ArrayList<String>()
        for (error in ex.bindingResult.fieldErrors) {
            errors.add(error.field + ": " + error.defaultMessage)
        }
        for (error in ex.bindingResult.globalErrors) {
            errors.add(error.objectName + ": " + error.defaultMessage)
        }

        val apiError = ApiError(HttpStatus.BAD_REQUEST, ex.localizedMessage, errors)
        return handleExceptionInternal(
                ex, apiError, headers, apiError.status!!, request)

    }
}
