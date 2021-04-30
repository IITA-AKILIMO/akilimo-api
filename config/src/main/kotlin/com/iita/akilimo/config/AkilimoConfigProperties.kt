package com.iita.akilimo.config

import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration

@Configuration
class AkilimoConfigProperties {

    @Bean
    @ConfigurationProperties(prefix = "akilimo.currency")
    fun currency(): CurrencyProperties {
        return CurrencyProperties()
    }


    @Bean
    @ConfigurationProperties(prefix = "akilimo.messaging")
    fun globalProperties(): MessagingProperties {
        return MessagingProperties()
    }

    @Bean
    @ConfigurationProperties(prefix = "akilimo.plumber")
    fun plumber(): PlumberProperties {
        return PlumberProperties()
    }

    @Bean
    @ConfigurationProperties(prefix = "messaging.sms")
    fun sms(): MessagingProperties {
        return MessagingProperties()
    }

    @Bean
    @ConfigurationProperties(prefix = "messaging.email")
    fun email(): MessagingProperties {
        return MessagingProperties()
    }
}
