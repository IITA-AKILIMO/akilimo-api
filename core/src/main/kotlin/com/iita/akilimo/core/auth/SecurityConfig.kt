package com.iita.akilimo.core.auth

import com.forgerock.spring.security.multiauth.configurers.MultiAuthenticationCollectorConfigurer
import com.forgerock.spring.security.multiauth.configurers.collectors.APIKeyCollector
import com.forgerock.spring.security.multiauth.configurers.collectors.CustomJwtCookieCollector
import com.forgerock.spring.security.multiauth.configurers.collectors.StaticUserCollector
import com.iita.akilimo.database.repos.UserAuthRepo
import com.nimbusds.jose.JWSObject
import com.nimbusds.jose.JWSVerifier
import com.nimbusds.jose.crypto.MACVerifier
import com.nimbusds.jwt.JWTParser
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import org.springframework.core.annotation.Order
import org.springframework.http.HttpMethod
import org.springframework.security.config.annotation.web.builders.HttpSecurity
import org.springframework.security.config.annotation.web.builders.WebSecurity
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity
import org.springframework.security.config.annotation.web.configuration.WebSecurityConfigurerAdapter
import org.springframework.security.config.http.SessionCreationPolicy
import org.springframework.security.core.GrantedAuthority
import org.springframework.security.core.authority.SimpleGrantedAuthority
import org.springframework.security.core.userdetails.User
import org.springframework.security.crypto.password.PasswordEncoder
import org.springframework.web.cors.CorsConfiguration
import org.springframework.web.cors.UrlBasedCorsConfigurationSource
import org.springframework.web.filter.CorsFilter
import java.util.stream.Collectors
import java.util.stream.Stream
import javax.sql.DataSource

@Configuration
@EnableWebSecurity
//@Order(1)
class SecurityConfig(
    val encoder: PasswordEncoder, val dataSource: DataSource, val authRepo: UserAuthRepo
) : WebSecurityConfigurerAdapter() {

    val myLogger: Logger = LoggerFactory.getLogger(this::class.java)

    private val API_KEY_AUTH_HEADER_NAME = "key"


    @Throws(java.lang.Exception::class)
    fun configureTrial(http: HttpSecurity) {

        val nonAuthPaths = arrayOf(
            "/api/v2/recommendations/**/**"
        )

        http
            .authorizeRequests()
            .anyRequest()
            .permitAll()
            .and()
            .sessionManagement()
            .sessionCreationPolicy(SessionCreationPolicy.STATELESS) //We don't need sessions to be created.
            .and()
            .apply(myAuthAdapter())

//        http.cors().and().authorizeRequests().antMatchers(*nonAuthPaths).permitAll()
//            .anyRequest()
//            .authenticated()
//            .and()
//            .sessionManagement()
//            .sessionCreationPolicy(SessionCreationPolicy.STATELESS) //We don't need sessions to be created.
//            .and()
//            .apply(myAuthAdapter())
//
        http.formLogin().disable()
//
        http.csrf().disable()
    }

    private fun myAuthAdapter(): MultiAuthenticationCollectorConfigurer<HttpSecurity> {

        /**
         * Authentication & authorisation via a cookie 'SSO'
         * The authorities are extracted from the 'group' claim
         * The username is extracted from the 'sub' claim
         * Note: JWT cookies expected to be signed with HMAC with "Qt5y2isMydGwVuREoIomK9Ei70EoFQKH0GpcbtJ4" as a secret
         */
        val jwTCollector =
            CustomJwtCookieCollector.builder().collectorName("Cookie-SSO").authoritiesCollector { token ->
                token.jwtClaimsSet.getStringListClaim("group").stream().map { g -> SimpleGrantedAuthority(g) }
                    .collect(Collectors.toSet()) as Set<GrantedAuthority>?
            }.tokenValidator { tokenSerialised ->
                val jwsObject = JWSObject.parse(tokenSerialised)
                val verifier: JWSVerifier = MACVerifier("Qt5y2isMydGwVuREoIomK9Ei70EoFQKH0GpcbtJ4")
                jwsObject.verify(verifier)
                JWTParser.parse(tokenSerialised)
            }.cookieName("SSO").build()


        val apiKeyCollector =
            APIKeyCollector.builder<User>().collectorName("API-Key").apiKeyExtractor { req -> req.getHeader("key") }
                .apiKeyValidator { apiKey ->
                    User(
                        "bob", "", Stream.of(SimpleGrantedAuthority("repo-42")).collect(Collectors.toSet())
                    )
                }.usernameCollector(User::getUsername)
                .authoritiesCollector { user -> HashSet<GrantedAuthority>(user.authorities) }.build()

        /**
         * Static authentication
         * If no authentication was possible with the previous collector, we default to the anonymous user
         */
        val staticCollector =
            StaticUserCollector.builder().collectorName("StaticUser-anonymous").usernameCollector { "anonymous" }
                .build()

        return MultiAuthenticationCollectorConfigurer<HttpSecurity>()
            .collectorForAuthorzation(jwTCollector)
            .collectorForAuthentication(apiKeyCollector)
//            .collector(staticCollector)
    }

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
        http.authorizeRequests().anyRequest().authenticated()
//            .antMatchers(HttpMethod.POST, "/api").hasRole("ADMIN")
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
            .hasAnyRole("ADMIN", "USER")
            .antMatchers(HttpMethod.PUT, "/api/**").hasRole("ADMIN").antMatchers(HttpMethod.DELETE, "/api/**")
            .hasRole("ADMIN")
            .antMatchers(HttpMethod.GET, "/api/**").hasAnyRole("ADMIN", "USER").and().sessionManagement()
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
