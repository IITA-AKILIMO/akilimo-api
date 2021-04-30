package com.iita.akilimo.api.config

import org.springframework.security.core.AuthenticationException
import org.springframework.security.web.authentication.www.BasicAuthenticationEntryPoint
import kotlin.Throws
import java.io.IOException
import javax.servlet.http.HttpServletRequest
import javax.servlet.http.HttpServletResponse
import java.io.PrintWriter

class CustomBasicAuthenticationEntryPoint : BasicAuthenticationEntryPoint() {
    @Throws(IOException::class)
    override fun commence(
        request: HttpServletRequest,
        response: HttpServletResponse,
        authException: AuthenticationException
    ) {
        //Authentication failed, send error response.
        response.status = HttpServletResponse.SC_UNAUTHORIZED
        response.addHeader("WWW-Authenticate", "Basic realm=$realmName")
        val writer = response.writer
        writer.println("HTTP Status 401 : " + authException.message)
    }

    override fun afterPropertiesSet() {
        realmName = "MY_TEST_REALM"
        super.afterPropertiesSet()
    }
}
