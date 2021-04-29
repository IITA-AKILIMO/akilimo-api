package com.iita.akilimo.service

import com.iita.akilimo.config.AkilimoConfigProperties
import com.iita.akilimo.core.mapper.RecommendationResponseDto
import com.iita.akilimo.core.service.MessagingService
import org.junit.jupiter.api.Assertions.assertEquals
import org.junit.jupiter.api.Test


internal class MessagingPropertiesServiceTest {

    private val recommendationResponseDto = RecommendationResponseDto()
    private val service = MessagingService(akilimoConfig = AkilimoConfigProperties())

    /*@Ignore
    @Test()
    fun sms_response_should_not_be_null() {
        recommendationResponseDto.country = "KE"
        recommendationResponseDto.mobileNumber = "0713196504"
        recommendationResponseDto.fertilizerRecText = "Fertilizer recommendation text"
        val smsResponse = service.sendTextMessage(recommendationResponseDto)


        Assertions.assertThrows(
                RetrofitError::class.java
        ) {
            smsResponse
        }
    }*/

    @Test
    fun testNumberFormatting() {
        recommendationResponseDto.country = "KE"
        recommendationResponseDto.mobileCountryCode = "254"
        recommendationResponseDto.mobileNumber = "0713196504"
        val phone = service.processPhoneNumber(recommendationResponseDto)

        assertEquals("Country Code: 254 National Number: 713196504", phone.toString())
    }

    @Test
    fun should_return_international_number() {
        recommendationResponseDto.country = "KE"
        recommendationResponseDto.mobileNumber = "0713196504"
        val phone = service.convertToInternationalNumber(recommendationResponseDto)

        assertEquals("+254713196504", phone)
    }
}
