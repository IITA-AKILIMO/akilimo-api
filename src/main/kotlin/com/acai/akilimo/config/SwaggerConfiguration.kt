package com.acai.akilimo.config

import com.google.common.base.Predicate
import com.google.common.base.Predicates
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import springfox.documentation.builders.ApiInfoBuilder
import springfox.documentation.builders.PathSelectors
import springfox.documentation.builders.RequestHandlerSelectors
import springfox.documentation.service.ApiInfo
import springfox.documentation.spi.DocumentationType
import springfox.documentation.spring.web.plugins.Docket
import springfox.documentation.swagger2.annotations.EnableSwagger2

@Configuration
@EnableSwagger2
class SwaggerConfiguration {
    @Bean
    fun akilimoApi(): Docket {

        return Docket(DocumentationType.SWAGGER_2)
                .apiInfo(apiInfo())
                .select()
                .apis(RequestHandlerSelectors.basePackage("com.acai.akilimo.controllers"))
                //.paths(regex("/api/v2/recommendations.*"))
                .paths(apiPaths())
                .build()
    }

    // Describe your apis
    private fun apiInfo(): ApiInfo {
        return ApiInfoBuilder()
                .title("Crop manager API")
                .description("This page lists all the endpoints the Akilimo api.")
                .version("2.0.0")
                .build()
    }

    private fun apiPaths(): Predicate<String> {
        return Predicates.and(
                PathSelectors.regex("/api/v2.*"),
                Predicates.not(PathSelectors.regex("/error.*"))
        )
    }
}
