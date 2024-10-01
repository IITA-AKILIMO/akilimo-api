package com.iita.akilimo.api.config

import com.iita.akilimo.api.interceptors.ApiKeyAuthFilter
import com.iita.akilimo.core.service.UserService
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import org.springframework.http.HttpMethod
import org.springframework.security.authentication.AuthenticationManager
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken
import org.springframework.security.config.annotation.authentication.builders.AuthenticationManagerBuilder
import org.springframework.security.config.annotation.web.builders.HttpSecurity
import org.springframework.security.config.annotation.web.builders.WebSecurity
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity
import org.springframework.security.config.annotation.web.configuration.WebSecurityConfigurerAdapter
import org.springframework.security.config.http.SessionCreationPolicy
import org.springframework.security.crypto.password.PasswordEncoder
import org.springframework.security.web.authentication.UsernamePasswordAuthenticationFilter
import org.springframework.web.cors.CorsConfiguration
import org.springframework.web.cors.UrlBasedCorsConfigurationSource
import org.springframework.web.filter.CorsFilter
import javax.sql.DataSource

@Configuration
@EnableWebSecurity
class SecurityConfig(
    val encoder: PasswordEncoder,
    val dataSource: DataSource,
    val userService: UserService
) : WebSecurityConfigurerAdapter() {


    @Autowired
    @Throws(Exception::class)
    fun configureGlobal(auth: AuthenticationManagerBuilder) {
//        auth.jdbcAuthentication().dataSource(dataSource)
//            .passwordEncoder(encoder)
        auth.userDetailsService(userService).passwordEncoder(encoder)
    }

    @Throws(Exception::class)
    override fun configure(http: HttpSecurity) {

        val nonAuthPaths = arrayOf(
            "/swagger-ui.html",
            "/swagger-ui/**",
            "/akilimo-api",
            "/akilimo-api/**",
            "/legacy/**",
            "/static/**",
            "/api/**/users/auth",
            "/api/**/users/reset",
            "/api/**/users/refresh",

            "/api/**/fert**", "/api/**/fert**/**",
            "/api/**/oper**", "/api/**/oper**/**",
            "/api/**/reco**",
            "/api/**/curr**",
            "/api/**/cass**", "/api/**/cass**/**",
            "/api/**/maize**", "/api/**/maize**/**",
            "/api/**/potato**", "/api/**/potato**/**",
            "/api/**/starch**",
            "/api/**/user-feedback**", "/api/**/user-feedback**/**",
        )

        http.cors().and().authorizeRequests().antMatchers(*nonAuthPaths).permitAll()
            .anyRequest().authenticated()
            .and().sessionManagement().sessionCreationPolicy(SessionCreationPolicy.STATELESS)

//        http.httpBasic()
//            .and()
//            .authorizeRequests()
//            .antMatchers(
//                "/api/**/fert**", "/api/**/fert**/**",
//                "/api/**/oper**", "/api/**/oper**/**",
//                "/api/**/reco**",
//                "/api/**/curr**",
//                "/api/**/cass**", "/api/**/cass**/**",
//                "/api/**/maize**", "/api/**/maize**/**",
//                "/api/**/potato**", "/api/**/potato**/**",
//                "/api/**/starch**",
//                "/api/**/user-feedback**", "/api/**/user-feedback**/**",
//            ).permitAll()
//            .antMatchers(HttpMethod.GET, "/api").hasRole("ADMIN")
//            .antMatchers(HttpMethod.POST, "/api/**").hasAnyRole("ADMIN", "USER")
//            .antMatchers(HttpMethod.PUT, "/api/**").hasRole("ADMIN")
//            .antMatchers(HttpMethod.DELETE, "/api/**").hasRole("ADMIN")
//            .antMatchers(HttpMethod.GET, "/api/**").hasAnyRole("ADMIN", "USER")
//            .and()
//            .sessionManagement()
//            .sessionCreationPolicy(SessionCreationPolicy.STATELESS) //We don't need sessions to be created.
//            .and()

        http.formLogin().disable()

        http.csrf().disable()

        //add auth filter
        http.addFilterBefore(authenticationJwtTokenFilter(), UsernamePasswordAuthenticationFilter::class.java)
    }

    // Used by spring security if CORS is enabled.
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

    /* To allow Pre-flight [OPTIONS] request from browser */
    @Throws(Exception::class)
    override fun configure(web: WebSecurity) {
        web.ignoring().antMatchers(HttpMethod.OPTIONS, "/**")
    }

    @Bean
    fun authenticationJwtTokenFilter(): ApiKeyAuthFilter {
        return ApiKeyAuthFilter()
    }


    @Bean
    override fun authenticationManagerBean(): AuthenticationManager {
        return super.authenticationManagerBean()
    }
}
