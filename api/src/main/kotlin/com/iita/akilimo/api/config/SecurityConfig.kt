package com.iita.akilimo.api.config

import org.springframework.beans.factory.annotation.Autowired
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import org.springframework.http.HttpMethod
import org.springframework.security.config.annotation.authentication.builders.AuthenticationManagerBuilder
import org.springframework.security.config.annotation.web.builders.HttpSecurity
import org.springframework.security.config.annotation.web.builders.WebSecurity
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity
import org.springframework.security.config.annotation.web.configuration.WebSecurityConfigurerAdapter
import org.springframework.security.config.http.SessionCreationPolicy
import org.springframework.security.crypto.password.PasswordEncoder

@Configuration
@EnableWebSecurity
class SecurityConfig(val encode: PasswordEncoder) : WebSecurityConfigurerAdapter() {

    companion object {
        private const val REALM = "MY_TEST_REALM"
    }

    @Autowired
    @Throws(Exception::class)
    fun configureGlobalSecurity(auth: AuthenticationManagerBuilder) {
        auth.inMemoryAuthentication().withUser("bill").password(encode.encode("abc123")).roles("ADMINs")
        auth.inMemoryAuthentication().withUser("tom").password(encode.encode("abc123")).roles("USER")
    }

//    @Throws(Exception::class)
//    override fun configure(http: HttpSecurity) {
//        http.csrf().disable()
//            .authorizeRequests()
//            .antMatchers("/api/**").hasRole("ADMIN")
//            .and().httpBasic().realmName(REALM).authenticationEntryPoint(basicAuthEntryPoint)
//            .and().sessionManagement().sessionCreationPolicy(SessionCreationPolicy.STATELESS) //We don't need sessions to be created.
//    }

    @Throws(Exception::class)
    override fun configure(http: HttpSecurity) {
        http.httpBasic()
            .and()
            .authorizeRequests()
            .antMatchers(HttpMethod.GET, "/api").hasRole("ADMIN")
            .antMatchers(HttpMethod.POST, "/api/**").hasRole("ADMIN")
            .antMatchers(HttpMethod.PUT, "/api/**").hasRole("ADMIN")
            .antMatchers(HttpMethod.DELETE, "/api/**").hasRole("ADMIN")
            .antMatchers(HttpMethod.GET, "/api/**").hasAnyRole("ADMIN", "DOCTOR")
            .and()
            .sessionManagement().sessionCreationPolicy(SessionCreationPolicy.STATELESS) //We don't need sessions to be created.
            .and()
            .csrf().disable()
            .formLogin().disable()
    }

    @get:Bean
    val basicAuthEntryPoint: CustomBasicAuthenticationEntryPoint
        get() = CustomBasicAuthenticationEntryPoint()

    /* To allow Pre-flight [OPTIONS] request from browser */
    @Throws(Exception::class)
    override fun configure(web: WebSecurity) {
        web.ignoring().antMatchers(HttpMethod.OPTIONS, "/**")
    }
}
