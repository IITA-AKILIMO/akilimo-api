package com.iita.akilimo.api.interceptors

import com.auth0.jwt.exceptions.SignatureVerificationException
import com.iita.akilimo.core.mapper.UserDetailsImpl
import com.iita.akilimo.core.service.UserService
import com.iita.akilimo.enums.EnumJwtClaims
import com.iita.security.utils.JwtUtils
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken
import org.springframework.security.core.context.SecurityContextHolder
import org.springframework.security.core.userdetails.UserDetails
import org.springframework.security.web.authentication.WebAuthenticationDetailsSource
import org.springframework.stereotype.Component
import org.springframework.web.filter.OncePerRequestFilter
import javax.servlet.FilterChain
import javax.servlet.http.HttpServletRequest
import javax.servlet.http.HttpServletResponse

@Component
class ApiKeyAuthFilter : OncePerRequestFilter() {

    @Autowired
    lateinit var userService: UserService

    @Autowired
    lateinit var jwtUtils: JwtUtils

    private val myLogger = LoggerFactory.getLogger(this::class.java)

    override fun doFilterInternal(
        request: HttpServletRequest, response: HttpServletResponse, filterChain: FilterChain
    ) {
        try {
            val decodedJWT = jwtUtils.decodeJwtToken(request)

            if (decodedJWT != null) {
                val username = decodedJWT.getClaim(EnumJwtClaims.USERNAME.name).asString()
                val entityUserDetails = userService.loadUserByUsername(username)
                if (jwtUtils.validateToken(request, entityUserDetails)) {
                    val userDetails = UserDetailsImpl(username = username, password = "")
                    val auth = UsernamePasswordAuthenticationToken(userDetails, null, userDetails.authorities)

                    auth.details = WebAuthenticationDetailsSource().buildDetails(request)
                    SecurityContextHolder.getContext().authentication = auth
                }
            }
        } catch (ex: SignatureVerificationException) {
            myLogger.error("Signature verification exception: {}", ex.message)
        } catch (e: Exception) {
            myLogger.error("General error: {}", e.message)
        }

        filterChain.doFilter(request, response)
    }
}
