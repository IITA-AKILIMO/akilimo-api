/**
 * Copyright 2019 Greg Whitaker
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

import com.github.benmanes.caffeine.cache.CacheLoader
import com.github.benmanes.caffeine.cache.Caffeine
import com.github.benmanes.caffeine.cache.LoadingCache
import com.iita.akilimo.core.exceptions.NotFoundException
import com.iita.akilimo.core.utils.UUIDUtil.fromHex
import com.iita.akilimo.database.repos.UserRepo
import org.slf4j.LoggerFactory
import org.springframework.security.authentication.AuthenticationManager
import org.springframework.security.authentication.BadCredentialsException
import org.springframework.security.core.Authentication
import org.springframework.security.core.AuthenticationException
import java.util.concurrent.TimeUnit
import javax.sql.DataSource

/**
 * Handles authenticating api keys against the database.
 */

class ApiKeyAuthManager(dataSource: DataSource, val userRepo: UserRepo) : AuthenticationManager {
    private val keys: LoadingCache<String, Boolean>

    companion object {
        private val logger = LoggerFactory.getLogger(this::class.java)
    }

    init {
        keys = Caffeine.newBuilder()
            .expireAfterAccess(5, TimeUnit.MINUTES)
            .build(DatabaseCacheLoader(dataSource, userRepo))
    }

    @Throws(AuthenticationException::class)
    override fun authenticate(authentication: Authentication): Authentication {
        val principal = authentication.principal as String
        return if (!keys[principal]) {
            throw BadCredentialsException("The API key was not found or not the expected value.")
        } else {
            authentication.isAuthenticated = true
            authentication
        }
    }

    /**
     * Caffeine CacheLoader that checks the database for the api key if it not found in the cache.
     */
    private class DatabaseCacheLoader(private val dataSource: DataSource, private val userRepo: UserRepo) :
        CacheLoader<String?, Boolean> {
        @Throws(Exception::class)
        override fun load(key: String?): Boolean {
            logger.info("Loading api key from database: [key: {}]", key)
            val user = userRepo.findById(1).orElseThrow { throw NotFoundException("Api key not found") }
            val hex = fromHex(key!!)
            return false
        }
    }
}
