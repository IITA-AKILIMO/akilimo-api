package com.iita.akilimo.core.auth

import com.iita.akilimo.database.repos.UserAuthRepo
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import org.springframework.core.annotation.Order
import org.springframework.http.HttpMethod
import org.springframework.security.config.annotation.web.builders.HttpSecurity
import org.springframework.security.config.annotation.web.builders.WebSecurity
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity
import org.springframework.security.config.annotation.web.configuration.WebSecurityConfigurerAdapter
import org.springframework.security.config.http.SessionCreationPolicy
import org.springframework.security.crypto.password.PasswordEncoder
import org.springframework.web.cors.CorsConfiguration
import org.springframework.web.cors.UrlBasedCorsConfigurationSource
import org.springframework.web.filter.CorsFilter
import javax.sql.DataSource

@Configuration
@EnableWebSecurity
@Order(1)
class SecurityConfig(
    val encoder: PasswordEncoder, val dataSource: DataSource, val authRepo: UserAuthRepo
) : WebSecurityConfigurerAdapter() {

    private val API_KEY_AUTH_HEADER_NAME = "key"

    @Throws(Exception::class)
    override fun configure(http: HttpSecurity) {

        val authPaths = arrayOf(
            "/swagger-ui/**", "/fuelrod-api/**"
        )

        val filter = ApiKeyAuthFilter(API_KEY_AUTH_HEADER_NAME)
        val apiKeyAuthManager = ApiKeyAuthManager(dataSource, authRepo)
        filter.setAuthenticationManager(apiKeyAuthManager)

        http.antMatcher("/api/v2/recommendations/**/**")

        http.addFilter(filter)
            .authorizeRequests()
            .anyRequest()
            .authenticated()
//            .antMatchers(HttpMethod.GET, "/api").hasRole("ADMIN")
//            .antMatchers(HttpMethod.POST, "/api/**").hasAnyRole("ADMIN", "USER")
//            .antMatchers(HttpMethod.PUT, "/api/**").hasRole("ADMIN")
//            .antMatchers(HttpMethod.DELETE, "/api/**").hasRole("ADMIN")
//            .antMatchers(HttpMethod.GET, "/api/**").hasAnyRole("ADMIN", "USER")

        http.sessionManagement()
            .sessionCreationPolicy(SessionCreationPolicy.STATELESS) //We don't need sessions to be created.
            .and().formLogin().disable()

        http.csrf().disable()
    }

    @Throws(Exception::class)
    fun configureOld(http: HttpSecurity) {

        http.httpBasic().and().authorizeRequests().antMatchers(
            "/api/**/fert**", "/api/**/fert**/**",
            "/api/**/oper**", "/api/**/oper**/**",
            "/api/**/reco**",
            "/api/**/curr**",
            "/api/**/cass**", "/api/**/cass**/**",
            "/api/**/maize**", "/api/**/maize**/**",
            "/api/**/potato**", "/api/**/potato**/**",
            "/api/**/starch**",
            "/api/**/user-feedback**", "/api/**/user-feedback**/**",
        ).permitAll().antMatchers(HttpMethod.GET, "/api").hasRole("ADMIN").antMatchers(HttpMethod.POST, "/api/**")
            .hasAnyRole("ADMIN", "USER").antMatchers(HttpMethod.PUT, "/api/**").hasRole("ADMIN")
            .antMatchers(HttpMethod.DELETE, "/api/**").hasRole("ADMIN").antMatchers(HttpMethod.GET, "/api/**")
            .hasAnyRole("ADMIN", "USER").and().sessionManagement()
            .sessionCreationPolicy(SessionCreationPolicy.STATELESS) //We don't need sessions to be created.
            .and().formLogin().disable()

        http.csrf().disable()
    }

    /* To allow Pre-flight [OPTIONS] request from browser */
    @Throws(Exception::class)
    override fun configure(web: WebSecurity) {
        web.ignoring().antMatchers(HttpMethod.OPTIONS, "/**")
    }

    @Bean
    fun corsFilter(): CorsFilter? {
        val source = UrlBasedCorsConfigurationSource()
        val config = CorsConfiguration()
        config.allowCredentials = false
        config.addAllowedOriginPattern("*")
        config.addAllowedHeader("*")
        config.addAllowedMethod("*")
        source.registerCorsConfiguration("/**", config)
        return CorsFilter(source)
    }
}
