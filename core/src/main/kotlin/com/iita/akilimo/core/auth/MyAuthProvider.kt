package com.iita.akilimo.core.auth

import com.iita.akilimo.core.exceptions.AuthorizationException
import com.iita.akilimo.core.service.UserService
import org.springframework.security.authentication.AuthenticationProvider
import org.springframework.security.core.Authentication
import org.springframework.stereotype.Component

//@Component
class MyAuthProvider(val userService: UserService) : AuthenticationProvider {

    override fun authenticate(authentication: Authentication): Authentication {
        //@link https://www.javadevjournal.com/spring-security/spring-security-multiple-authentication-providers/
        val username = authentication.name
        if (username.isNullOrEmpty()) {
            throw AuthorizationException("Invalid login details")
        }

//        val user = userService.loadUserByUsername(username)
        return authentication
    }

    override fun supports(authentication: Class<*>?): Boolean {
        TODO("Not yet implemented")
    }
}