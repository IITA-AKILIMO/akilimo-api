package com.iita.akilimo.api.controllers

import org.springframework.web.bind.annotation.RestControllerAdvice
import org.springframework.web.servlet.mvc.method.annotation.ResponseEntityExceptionHandler


@RestControllerAdvice
class BaseController : ResponseEntityExceptionHandler()
