package com.iita.akilimo.core.service

import com.iita.akilimo.core.request.UserRequest
import com.iita.akilimo.core.response.LoginResponse
import com.iita.akilimo.database.entities.ApiToken
import com.iita.akilimo.database.repos.ApiTokenRepo
import com.iita.security.utils.JwtUtils
import org.slf4j.LoggerFactory
import org.springframework.security.core.Authentication
import org.springframework.security.core.context.SecurityContextHolder
import org.springframework.security.core.userdetails.UserDetails
import org.springframework.stereotype.Service
import java.time.ZoneId

@Service
class AuthService(
    private val jwtUtils: JwtUtils,
    private val apiTokenRepo: ApiTokenRepo
) {

    private val logger = LoggerFactory.getLogger(this::class.java)

    fun auth(loginRequest: UserRequest, authentication: Authentication, remoteIp: String): LoginResponse {

        SecurityContextHolder.getContext().authentication = authentication
        val userDetails = authentication.principal as UserDetails

        logger.debug("Extracting origin ip address {}", remoteIp)
        var isExpired = true
        val refreshToken = jwtUtils.createRefreshToken(userDetails.username)


        val userToken: ApiToken =
            apiTokenRepo.findByUserNameAndIpAddress(userName = userDetails.username, ipAddress = remoteIp).orElse(
                ApiToken()
            )

        if (userToken.token.isNullOrBlank()) {
            userToken.userName = refreshToken.username
            userToken.ipAddress = remoteIp
        } else {
            isExpired = jwtUtils.isRefreshTokenExpired(userToken.expiryDate!!)
        }
        if (isExpired) {
            logger.debug("Token is expired, refreshing it")
            userToken.token = refreshToken.token
            userToken.expiryDate = refreshToken.expiryDate
        }

        val resp = apiTokenRepo.save(userToken)

        val accessToken = jwtUtils.createJwtToken(resp.userName!!, resp.id!!)

        return LoginResponse(
            accessToken = accessToken,
            expiry = resp.expiryDate!!.atZone(ZoneId.systemDefault()).toEpochSecond(),
            expiryDate = resp.expiryDate!!
        )
    }
}
