package com.acai.akilimo.config

import com.acai.akilimo.properties.Plumber
import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration


@Configuration
class ConfigProperties {

    @Bean
    @ConfigurationProperties(prefix = "plumber")
    fun plumber(): Plumber {
        return Plumber()
    }
}