package com.acai.akilimo.config

import com.google.common.base.Predicate
import com.google.common.base.Predicates
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import springfox.documentation.builders.ApiInfoBuilder
import springfox.documentation.builders.ParameterBuilder
import springfox.documentation.builders.PathSelectors
import springfox.documentation.builders.RequestHandlerSelectors
import springfox.documentation.schema.ModelRef
import springfox.documentation.service.ApiInfo
import springfox.documentation.service.Parameter
import springfox.documentation.spi.DocumentationType
import springfox.documentation.spring.web.plugins.Docket
import springfox.documentation.swagger2.annotations.EnableSwagger2
import java.util.*


@Configuration
@EnableSwagger2
class SwaggerConfiguration {
    @Bean
    fun api(): Docket {
        return Docket(DocumentationType.SWAGGER_2)
                .apiInfo(apiInfo())
                .select()
                .apis(RequestHandlerSelectors.basePackage("com.acai.akilimo.controllers"))
                .paths(apiPaths())
                .build()
//                .globalOperationParameters(globalParameters())
    }

    // Describe your apis
    private fun apiInfo(): ApiInfo {
        return ApiInfoBuilder()
                .title("Akilimo API")
                .description("This page lists all the endpoints the Akilimo api.")
                .version("3.0.0")
                .build()
    }

    private fun apiPaths(): Predicate<String> {
        return Predicates.and(
//                PathSelectors.regex("/api/v2.*"),
//                PathSelectors.regex("/api/v3.*"),
                PathSelectors.regex("/api/*.*"),
                Predicates.not(PathSelectors.regex("/error.*"))
        )
    }

    private fun globalParameters(): MutableList<Parameter> {
        val aParameterBuilder = ParameterBuilder()
        aParameterBuilder.name("Authorization") // name of header
                .modelRef(ModelRef("string"))
                .parameterType("header") // type - header
                .defaultValue("Basic em9uZTpteXBhc3N3b3Jk") // based64 of - zone:mypassword
                .required(true) // for compulsory
                .build()


        val aParameters: MutableList<Parameter> = ArrayList<Parameter>()
        aParameters.add(aParameterBuilder.build())
        return aParameters;
    }
}
