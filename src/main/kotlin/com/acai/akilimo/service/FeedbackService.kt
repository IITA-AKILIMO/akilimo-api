package com.acai.akilimo.service


import com.acai.akilimo.entities.UserFeedback
import com.acai.akilimo.repositories.UserFeedBackRepository
import com.acai.akilimo.request.SurveyRequest
import com.acai.akilimo.utils.CurrencyConversion
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service

@Suppress("DuplicatedCode")
@Service
class FeedbackService
@Autowired
constructor(
    private val userFeedBackRepository: UserFeedBackRepository
) {
    private val logger = LoggerFactory.getLogger(FeedbackService::class.java)

    private val conversion: CurrencyConversion = CurrencyConversion()

    private val modelMapper = ModelMapper()

    fun addUserFeedBack(surveyRequest: SurveyRequest): UserFeedback {
        val entity = modelMapper.map(surveyRequest, UserFeedback::class.java)
        return userFeedBackRepository.save(entity)
    }


}