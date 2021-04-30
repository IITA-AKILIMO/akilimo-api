package com.iita.akilimo.api.apierror

import io.swagger.v3.oas.annotations.enums.SecuritySchemeIn
import io.swagger.v3.oas.annotations.enums.SecuritySchemeType
import io.swagger.v3.oas.annotations.security.SecurityScheme
import io.swagger.v3.oas.models.ExternalDocumentation
import io.swagger.v3.oas.models.OpenAPI
import io.swagger.v3.oas.models.info.Contact
import io.swagger.v3.oas.models.info.Info
import io.swagger.v3.oas.models.info.License
import org.springframework.beans.factory.annotation.Value
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration

@Configuration
@SecurityScheme(name = "api", scheme = "basic", type = SecuritySchemeType.HTTP, `in` = SecuritySchemeIn.HEADER)
class OpenApiConfig {
    @Bean
    fun api(
        @Value("\${akilimo.version}") appVersion: String,
        @Value("\${akilimo.name}") appName: String,
        @Value("\${akilimo.license}") license: String,
    ): OpenAPI {
        return OpenAPI()
            .info(
                Info().title(appName)
                    .description(appName)
                    .contact(Contact().email("dev@munywele.co.ke").name("Munywele Consulting LTD").url("https://munywele.co.ke"))
                    .version(appVersion)
                    .license(License().name(license).url("https://www.gnu.org/licenses/gpl-3.0.en.html"))
            )
            .externalDocs(
                ExternalDocumentation()
                    .description("$appName Documentation")
                    .url("#")
            )
    }
}
