package com.acai.akilimo.controllers

import org.springframework.boot.web.servlet.error.ErrorController
import org.springframework.http.HttpStatus
import org.springframework.web.bind.annotation.RequestMapping
import org.springframework.web.bind.annotation.ResponseBody
import javax.servlet.RequestDispatcher
import javax.servlet.http.HttpServletRequest

//@Controller
class MyErrorController : ErrorController {

    @RequestMapping("/error")
    @ResponseBody
    fun handleError(request: HttpServletRequest): String {
        val status = request.getAttribute(RequestDispatcher.ERROR_STATUS_CODE)

        if (status != null) {
            val statusCode = Integer.valueOf(status.toString())
            if (statusCode == HttpStatus.NOT_FOUND.value()) {
                return "error-404"
            } else if (statusCode == HttpStatus.INTERNAL_SERVER_ERROR.value()) {
                return "error-500"
            }
        }
        return "error"
    }

    override fun getErrorPath(): String {
        return "/error"
    }
}
