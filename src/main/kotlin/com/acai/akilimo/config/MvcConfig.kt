package com.acai.akilimo.config

import com.acai.akilimo.interceptors.RateLimitingInterceptor
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.beans.factory.annotation.Configurable
import org.springframework.context.annotation.Configuration
import org.springframework.web.servlet.config.annotation.InterceptorRegistry
import org.springframework.web.servlet.config.annotation.WebMvcConfigurer

@Configuration
class MvcConfig : WebMvcConfigurer {

    @Autowired
    var rateLimitingInterceptor: RateLimitingInterceptor? = null

    override fun addInterceptors(registry: InterceptorRegistry) {
        registry.addInterceptor(rateLimitingInterceptor!!)
    }
}
