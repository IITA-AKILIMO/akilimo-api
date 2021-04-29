package com.iita.akilimo.core.service


import com.iita.akilimo.core.request.UserFeedBackRequest
import com.iita.akilimo.core.utils.CurrencyConversion
import com.iita.akilimo.database.repos.UserFeedBackRepository
import com.iita.akilimo.database.entities.UserFeedback
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service

@Suppress("DuplicatedCode")
@Service
class FeedbackService
constructor(
    val userFeedBackRepository: UserFeedBackRepository
) {
    private val logger = LoggerFactory.getLogger(FeedbackService::class.java)

    private val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()

    fun addUserFeedBack(userFeedBackRequest: UserFeedBackRequest): UserFeedback {
        val entity = modelMapper.map(userFeedBackRequest, UserFeedback::class.java)
        return userFeedBackRepository.save(entity)
    }


}
