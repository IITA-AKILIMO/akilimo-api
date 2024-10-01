package com.iita.akilimo.api.interceptors

import com.iita.akilimo.database.repos.ApiTokenRepo
import org.springframework.stereotype.Component
import org.springframework.web.filter.OncePerRequestFilter
import javax.servlet.FilterChain
import javax.servlet.http.HttpServletRequest
import javax.servlet.http.HttpServletResponse

@Component
class ApiKeyAuthFilter(
    private val apiTokenRepo: ApiTokenRepo
) : OncePerRequestFilter() {
    override fun doFilterInternal(
        request: HttpServletRequest,
        response: HttpServletResponse,
        filterChain: FilterChain
    ) {
        val apiKey = request.getHeader("Authorization")?.replace("Bearer ", "") ?: ""

    }
}
