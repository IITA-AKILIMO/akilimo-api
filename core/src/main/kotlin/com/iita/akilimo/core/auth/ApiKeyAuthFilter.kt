/**
 * Copyright 2022 Sammy Munywele
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package com.iita.akilimo.core.auth

import com.iita.akilimo.core.exceptions.AuthorizationException
import org.slf4j.LoggerFactory
import org.springframework.security.web.authentication.preauth.AbstractPreAuthenticatedProcessingFilter
import javax.servlet.FilterChain
import javax.servlet.ServletRequest
import javax.servlet.ServletResponse
import javax.servlet.http.HttpServletRequest

/**
 * Filter responsible for getting the api key off of incoming requests that need to be authorized.
 */
class ApiKeyAuthFilter(private val headerName: String) : AbstractPreAuthenticatedProcessingFilter() {

    private val myLogger = LoggerFactory.getLogger(this::class.java)


    override fun doFilter(request: ServletRequest?, response: ServletResponse?, chain: FilterChain?) {
        try {
            super.doFilter(request, response, chain)
        } catch (ex: Exception) {
            throw ex
        }
    }

    override fun getPreAuthenticatedPrincipal(request: HttpServletRequest): Any {

        var parameterKey = request.getHeader(headerName)
        if (parameterKey.isNullOrEmpty()) {
            parameterKey = request.getParameter(headerName)
        }

        if (parameterKey.isNullOrEmpty()) {
            throw AuthorizationException("Missing or invalid API KEY")
        }
        return parameterKey
    }

    override fun getPreAuthenticatedCredentials(request: HttpServletRequest?): Any {
        myLogger.info("No credentials when using API key")
        return ""
    }
}
