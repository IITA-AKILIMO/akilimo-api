package com.acai.akilimo.interfaces

import com.acai.akilimo.mapper.RecommendationResponseDto
import infobip.api.model.sms.mt.send.SMSResponse
import retrofit.RetrofitError

interface IMessagingService {
    fun sendEmailMessage(response: RecommendationResponseDto)

    @Throws(RetrofitError::class)
    fun sendTextMessage(response: RecommendationResponseDto): SMSResponse?
}