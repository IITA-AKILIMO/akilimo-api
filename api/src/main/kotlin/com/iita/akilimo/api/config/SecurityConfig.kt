package com.iita.akilimo.api.config

import org.springframework.beans.factory.annotation.Autowired
import org.springframework.context.annotation.Configuration
import org.springframework.http.HttpMethod
import org.springframework.security.config.annotation.authentication.builders.AuthenticationManagerBuilder
import org.springframework.security.config.annotation.web.builders.HttpSecurity
import org.springframework.security.config.annotation.web.builders.WebSecurity
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity
import org.springframework.security.config.annotation.web.configuration.WebSecurityConfigurerAdapter
import org.springframework.security.config.http.SessionCreationPolicy
import org.springframework.security.crypto.password.PasswordEncoder
import javax.sql.DataSource

@Configuration
@EnableWebSecurity
class SecurityConfig(
    val encoder: PasswordEncoder,
    val dataSource: DataSource
) : WebSecurityConfigurerAdapter() {


    @Autowired
    @Throws(Exception::class)
    fun configureGlobal(auth: AuthenticationManagerBuilder) {
        auth.jdbcAuthentication().dataSource(dataSource)
            .passwordEncoder(encoder)
    }

    @Throws(Exception::class)
    override fun configure(http: HttpSecurity) {

        http.httpBasic()
            .and()
            .authorizeRequests()
            .antMatchers(
                "/api/**/fert**","/api/**/fert**/**",
                "/api/**/oper**", "/api/**/oper**/**",
                "/api/**/reco**",
                "/api/**/curr**",
                "/api/**/cass**", "/api/**/cass**/**",
                "/api/**/maize**", "/api/**/maize**/**",
                "/api/**/potato**", "/api/**/potato**/**",
                "/api/**/starch**"
            ).permitAll()
            .antMatchers(HttpMethod.GET, "/api").hasRole("ADMIN")
            .antMatchers(HttpMethod.POST, "/api/**").hasRole("ADMIN")
            .antMatchers(HttpMethod.PUT, "/api/**").hasRole("ADMIN")
            .antMatchers(HttpMethod.DELETE, "/api/**").hasRole("ADMIN")
            .antMatchers(HttpMethod.GET, "/api/**").hasAnyRole("ADMIN", "USER")
            .and()
            .sessionManagement().sessionCreationPolicy(SessionCreationPolicy.STATELESS) //We don't need sessions to be created.
            .and()
            .formLogin().disable()

        http.csrf().disable()
    }

    /* To allow Pre-flight [OPTIONS] request from browser */
    @Throws(Exception::class)
    override fun configure(web: WebSecurity) {
        web.ignoring().antMatchers(HttpMethod.OPTIONS, "/**")
    }
}
