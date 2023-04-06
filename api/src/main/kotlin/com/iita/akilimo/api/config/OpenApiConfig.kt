package com.iita.akilimo.api.config

import io.swagger.v3.oas.annotations.enums.SecuritySchemeIn
import io.swagger.v3.oas.annotations.enums.SecuritySchemeType
import io.swagger.v3.oas.annotations.security.SecurityScheme
import io.swagger.v3.oas.models.OpenAPI
import io.swagger.v3.oas.models.info.Contact
import io.swagger.v3.oas.models.info.Info
import io.swagger.v3.oas.models.info.License
import io.swagger.v3.oas.models.security.SecurityRequirement
import io.swagger.v3.oas.models.servers.Server
import org.springframework.beans.factory.annotation.Value
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import java.util.*

@Configuration
@SecurityScheme(name = "key", type = SecuritySchemeType.APIKEY, `in` = SecuritySchemeIn.QUERY)
class OpenApiConfig {
    @Bean
    fun api(
        @Value("\${akilimo.version}") appVersion: String,
        @Value("\${akilimo.name}") appName: String,
        @Value("\${akilimo.license}") license: String,
        @Value("\${akilimo.dev-server}") devServer: String,
        @Value("\${akilimo.license_url}") licenseUrl: String,
        @Value("\${akilimo.developer.name}") developer: String,
        @Value("\${akilimo.developer.email}") developerEmail: String,
        @Value("\${akilimo.developer.url}") developerUrl: String,
    ): OpenAPI {
        val servers = mutableListOf<Server>()
        servers.add(Server().url(devServer))
        servers.add(Server().url("https://api.akilimo.org"))

        val apiInfo = Info().title(appName).description(appName).contact(
            Contact().email(developerEmail).name(developer).url(developerUrl)
        ).version(appVersion).license(License().name(license).url(licenseUrl))

        return OpenAPI().info(apiInfo).security(Collections.singletonList(SecurityRequirement().addList("key"))).servers(servers)
    }
}
