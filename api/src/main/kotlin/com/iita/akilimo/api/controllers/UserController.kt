package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.AuthorityDto
import com.iita.akilimo.core.mapper.UserDto
import com.iita.akilimo.core.request.AuthorityRequest
import com.iita.akilimo.core.request.UserRequest
import com.iita.akilimo.core.response.LoginResponse
import com.iita.akilimo.core.service.AuthService
import com.iita.akilimo.core.service.UserService
import io.swagger.v3.oas.annotations.Operation
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.security.authentication.AuthenticationManager
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken
import org.springframework.web.bind.annotation.*
import javax.servlet.http.HttpServletRequest
import javax.validation.Valid


@RequestMapping("/api/v1/users")
@RestController
class UserController(
    val userService: UserService,
    private val authService: AuthService,
    private val authenticationManager: AuthenticationManager,
) {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(this::class.java)
    }

    @PostMapping("/auth")
    fun authUser(
        @Valid @RequestBody userRequest: UserRequest,
        request: HttpServletRequest,
        @RequestHeader headers: Map<String, String>
    ): ResponseEntity<LoginResponse> {
        val authentication = authenticationManager.authenticate(
            UsernamePasswordAuthenticationToken(userRequest.username, userRequest.password)
        )

        val remoteIp = headers.getOrDefault("x-real-ip", request.remoteAddr);

        val userResp = authService.auth(
            loginRequest = userRequest,
            authentication = authentication,
            remoteIp = remoteIp
        )

        return ResponseEntity<LoginResponse>(userResp, HttpStatus.OK)

    }

    @PostMapping
    @Operation(summary = "Add new api user", description = "", tags = ["User"])
    fun addUser(@Valid @RequestBody userRequest: UserRequest): ResponseEntity<UserDto> {
        val userResp = userService.addUser(userRequest)

        return ResponseEntity(userResp, HttpStatus.OK)
    }

    @GetMapping
    @Operation(summary = "List users", description = "", tags = ["User"])
    fun listUsers(): ResponseEntity<List<UserDto>> {
        val userResp = userService.listUsers()

        return ResponseEntity(userResp, HttpStatus.CREATED)
    }


    @PostMapping("/{id}/user-role")
    @Operation(summary = "Add authority to a specific user", description = "", tags = ["User"])
    fun addUserAuthority(
        @PathVariable id: Long,
        @Valid @RequestBody authorityRequest: AuthorityRequest
    ): ResponseEntity<AuthorityDto> {
        val userResp = userService.addUserAuthority(authorityRequest)

        return ResponseEntity(userResp, HttpStatus.CREATED)
    }

    @GetMapping("/{id}")
    @Operation(summary = "Find user by id", description = "", tags = ["User"])
    fun findById(@PathVariable id: Long): ResponseEntity<UserDto> {
        val userResp = userService.findUser(id)

        return ResponseEntity(userResp, HttpStatus.OK)
    }

    @DeleteMapping("/{id}")
    @Operation(summary = "Delete user by id", description = "", tags = ["User"])
    fun deleteUser(@PathVariable id: Long): ResponseEntity<UserDto> {
        userService.deleteUser(id)
        return ResponseEntity(HttpStatus.OK)
    }

    @GetMapping("/{username}/username")
    @Operation(summary = "find user by username", description = "", tags = ["User"])
    fun findByUserName(@PathVariable username: String): ResponseEntity<UserDto> {
        val userResp = userService.findUserByUserName(username)

        return ResponseEntity(userResp, HttpStatus.OK)
    }
}
