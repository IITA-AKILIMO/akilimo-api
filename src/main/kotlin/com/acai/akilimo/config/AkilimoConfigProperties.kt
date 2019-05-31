package com.acai.akilimo.config

import com.acai.akilimo.properties.MessagingProperties
import com.acai.akilimo.properties.PlumberProperties
import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration


@Configuration
class AkilimoConfigProperties {

    @Bean
    @ConfigurationProperties(prefix = "plumber")
    fun plumber(): PlumberProperties {
        return PlumberProperties()
    }

    @Bean
    @ConfigurationProperties(prefix = "messaging")
    fun globalProperties(): MessagingProperties {
        return MessagingProperties()
    }


    @Bean
    @ConfigurationProperties(prefix = "messaging.plivo")
    fun plivoSms(): MessagingProperties {
        return MessagingProperties()
    }

    @Bean
    @ConfigurationProperties(prefix = "messaging.infobip")
    fun infoBipSms(): MessagingProperties {
        return MessagingProperties()
    }

    @Bean
    @ConfigurationProperties(prefix = "messaging.email")
    fun email(): MessagingProperties {
        return MessagingProperties()
    }
}