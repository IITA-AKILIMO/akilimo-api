package com.iita.akilimo.core.service

import com.iita.akilimo.core.mapper.AuthorityDto
import com.iita.akilimo.core.mapper.UserDto
import com.iita.akilimo.core.request.AuthorityRequest
import com.iita.akilimo.core.request.UserRequest
import com.iita.akilimo.database.entities.AuthorityEntity
import com.iita.akilimo.database.entities.UserEntity
import com.iita.akilimo.database.repos.AuthorityRepo
import com.iita.akilimo.database.repos.UserRepo
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.security.crypto.password.PasswordEncoder
import org.springframework.stereotype.Service

@Service
class UserService(
    val userRepo: UserRepo,
    val authorityRepo: AuthorityRepo,
    val encoder: PasswordEncoder
) {

    private val logger = LoggerFactory.getLogger(UserService::class.java)
    private val modelMapper = ModelMapper()

    fun addUser(userRequest: UserRequest): UserDto {
        val userEntity = modelMapper.map(userRequest, UserEntity::class.java)
        val encodedPass = encoder.encode(userRequest.password)
        userEntity.password = encodedPass

        val entity = userRepo.save(userEntity)
        return modelMapper.map(entity, UserDto::class.java)
    }

    fun addUserAuthority(authorityRequest: AuthorityRequest): AuthorityDto {
        val authorityEntity = modelMapper.map(authorityRequest, AuthorityEntity::class.java)

        val entity = authorityRepo.save(authorityEntity)
        return modelMapper.map(entity, AuthorityDto::class.java)
    }

    fun findUser(id: Long): UserDto {
        val user = userRepo.findById(id)
        return modelMapper.map(user.get(), UserDto::class.java)
    }

    fun findUserByUserName(username: String): UserDto {
        val user = userRepo.findByUsername(username)
        return modelMapper.map(user, UserDto::class.java)
    }

}
