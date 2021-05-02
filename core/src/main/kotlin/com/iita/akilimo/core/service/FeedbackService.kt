package com.iita.akilimo.core.service


import com.iita.akilimo.core.request.UserFeedBackRequest
import com.iita.akilimo.database.entities.UserFeedback
import com.iita.akilimo.database.repos.UserFeedBackRepo
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.stereotype.Service
import org.springframework.util.MultiValueMap

@Suppress("DuplicatedCode")
@Service
class FeedbackService
constructor(
    val userFeedBackRepo: UserFeedBackRepo
) {
    private val logger = LoggerFactory.getLogger(FeedbackService::class.java)

    private val modelMapper = ModelMapper()

    fun addUserFeedBack(userFeedBackRequest: UserFeedBackRequest): UserFeedback {
        val entity = modelMapper.map(userFeedBackRequest, UserFeedback::class.java)
        return userFeedBackRepo.save(entity)
    }

    fun listFeedBack(pageable: Pageable): Page<UserFeedback> {

        val feedBackList = userFeedBackRepo.findAllByOrderByIdDesc(pageable)

        return feedBackList

    }


}
