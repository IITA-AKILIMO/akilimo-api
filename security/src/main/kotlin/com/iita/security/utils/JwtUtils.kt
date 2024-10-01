package com.iita.security.utils

import com.auth0.jwt.JWT
import com.auth0.jwt.JWTVerifier
import com.auth0.jwt.algorithms.Algorithm
import com.auth0.jwt.exceptions.JWTCreationException
import com.auth0.jwt.exceptions.JWTVerificationException
import com.auth0.jwt.interfaces.DecodedJWT
import com.iita.akilimo.enums.EnumJwtClaims
import com.iita.security.RefreshToken
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Value
import org.springframework.security.core.userdetails.UserDetails
import org.springframework.stereotype.Component
import java.security.SecureRandom
import java.time.LocalDateTime
import java.time.ZoneId
import java.util.*
import javax.servlet.http.HttpServletRequest

@Component
class JwtUtils {

    @Value("\${jwt.secret}")
    private val secret: String? = null

    @Value("\${jwt.token-validity}")
    private val tokenValidity: Long = 0L

    @Value("\${jwt.issuer}")
    private val issuer: String? = null

    private val logger = LoggerFactory.getLogger(this::class.java)


    private val secureRandom = SecureRandom()

    fun createJwtToken(username: String, refreshTokenId: String): String {
        try {
            val algorithm: Algorithm = Algorithm.HMAC256(secret)

            return JWT.create()
                .withSubject(username)
                .withClaim(EnumJwtClaims.USERNAME.name, username)
                .withClaim(EnumJwtClaims.ID.name, refreshTokenId)
                .withIssuer(issuer)
                .withAudience(username)
                .withExpiresAt(getExpirationDate(tokenValidity))
                .sign(algorithm)
        } catch (exception: JWTCreationException) {
            throw exception
        }
    }

    fun createRefreshToken(username: String): RefreshToken {
        val token = generateRandomToken()
        val expiryDate = calculateExpiryDate()
        val id = generateUUID()

        return RefreshToken(id, username, token, expiryDate)
    }

    fun verify(token: String): DecodedJWT? {
        return try {
            val algorithm = Algorithm.HMAC256(secret)
            val verifier: JWTVerifier = JWT.require(algorithm)
                .withIssuer(issuer)
                .build()
            verifier.verify(token)
        } catch (exception: JWTVerificationException) {
            logger.error("JWT cannot be verified because {}", exception.localizedMessage)
            throw exception
        }
    }

    fun decodeJwtToken(request: HttpServletRequest): DecodedJWT? {
        val jwt = parseJwt(request)
        return jwt?.let { verify(it) }
    }

    fun decodeJwtToken(request: String?): DecodedJWT? {
        return request?.let { verify(it) }
    }

    private fun parseJwt(request: HttpServletRequest): String? {
        val headerAuth = request.getHeader("Authorization")
        return if (headerAuth != null && headerAuth.startsWith("Bearer ")) {
            headerAuth.substring(7)
        } else null
    }

    private fun getExpirationDate(expirationInSeconds: Long): Date {
        val localDate = LocalDateTime.now().plusSeconds(expirationInSeconds)
        return Date.from(localDate.atZone(ZoneId.systemDefault()).toInstant())
    }

    fun validateToken(request: HttpServletRequest, userDetails: UserDetails): Boolean {
        val username = getUsernameFromToken(request)
        return username == userDetails.username && !isTokenExpired(parseJwt(request)!!)
    }


    fun isRefreshTokenExpired(expiryDate: LocalDateTime): Boolean {
        return expiryDate.isBefore(LocalDateTime.now())
    }

    private fun isTokenExpired(token: String): Boolean {
        val expiration = getExpirationDateFromToken(token)
        return expiration.before(Date())
    }

    fun getExpirationDateFromToken(token: String?): Date {
        val decodedJWT = verify(token ?: return Date()) ?: return Date()
        return decodedJWT.expiresAt
    }

    fun getUsernameFromToken(request: HttpServletRequest): String {
        val token = parseJwt(request) ?: return ""
        val decodedJWT = verify(token)

        return decodedJWT?.claims?.get(EnumJwtClaims.USERNAME.name)?.asString() ?: ""
    }

    fun generateUUID(): String {
        return UUID.randomUUID().toString()
    }

    private fun generateRandomToken(): String {
        val bytes = ByteArray(64)
        secureRandom.nextBytes(bytes)
        return String(Base64.getEncoder().encode(bytes))
    }

    private fun calculateExpiryDate(): LocalDateTime {
        return LocalDateTime.now().plusSeconds(tokenValidity)
    }

}
