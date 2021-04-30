package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.AuthorityDto
import com.iita.akilimo.core.mapper.UserDto
import com.iita.akilimo.core.request.AuthorityRequest
import com.iita.akilimo.core.request.UserRequest
import com.iita.akilimo.core.service.UserService
import io.swagger.v3.oas.annotations.Operation
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping("/api/v1/user")
@RestController
class UserController(val userService: UserService) {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(UserController::class.java)
    }

    @PostMapping
    @Operation(summary = "Add new api user", description = "", tags = ["User"])
    fun addUser(@Valid @RequestBody userRequest: UserRequest): ResponseEntity<UserDto> {
        val userResp = userService.addUser(userRequest)

        return ResponseEntity(userResp, HttpStatus.CREATED)
    }

    @PostMapping("/authority/{id}")
    @Operation(summary = "Add authority to a specific user", description = "", tags = ["User"])
    fun addUserAuthority(@PathVariable id: Long, @Valid @RequestBody authorityRequest: AuthorityRequest): ResponseEntity<AuthorityDto> {
        val userResp = userService.addUserAuthority(authorityRequest)

        return ResponseEntity(userResp, HttpStatus.CREATED)
    }

    @GetMapping("/{id}")
    @Operation(summary = "Find user by id", description = "", tags = ["User"])
    fun findById(@PathVariable id: Long): ResponseEntity<UserDto> {
        val userResp = userService.findUser(id)

        return ResponseEntity(userResp, HttpStatus.OK)
    }

    @GetMapping
    @Operation(summary = "find user by username", description = "", tags = ["User"])
    fun findByUserName(@RequestParam(value = "username") username: String): ResponseEntity<UserDto> {
        val userResp = userService.findUserByUserName(username)

        return ResponseEntity(userResp, HttpStatus.OK)
    }
}
