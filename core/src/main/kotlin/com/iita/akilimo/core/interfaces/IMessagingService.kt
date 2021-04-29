package com.iita.akilimo.core.interfaces

import com.iita.akilimo.core.mapper.RecommendationResponseDto


interface IMessagingService {
    fun sendEmailMessage(response: RecommendationResponseDto, email: Boolean)

    fun sendTextMessage(response: RecommendationResponseDto, sendSms: Boolean)
}
