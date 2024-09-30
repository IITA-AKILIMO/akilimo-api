package com.iita.akilimo.core.service

import com.fasterxml.jackson.databind.ObjectMapper
import com.google.i18n.phonenumbers.PhoneNumberUtil
import com.google.i18n.phonenumbers.Phonenumber
import com.iita.akilimo.config.AkilimoConfigProperties
import com.iita.akilimo.config.MessagingProperties
import com.iita.akilimo.core.interfaces.IMessagingService
import com.iita.akilimo.core.mapper.RecommendationResponseDto
import com.iita.akilimo.core.request.SmsMessage
import com.iita.akilimo.core.response.MessageSendingResponse
import org.slf4j.LoggerFactory
import org.springframework.http.HttpEntity
import org.springframework.http.HttpHeaders
import org.springframework.http.MediaType
import org.springframework.stereotype.Service
import org.springframework.web.client.RestTemplate

@Service
class MessagingService(private val akilimoConfig: AkilimoConfigProperties) : IMessagingService {

    private val logger = LoggerFactory.getLogger(MessagingService::class.java)
    private val sms: MessagingProperties = akilimoConfig.sms()
    private val phoneUtil = PhoneNumberUtil.getInstance()
    private val mapper = ObjectMapper()
    private val restTemplate = RestTemplate()

    override fun sendTextMessage(response: RecommendationResponseDto, sendSms: Boolean) {
        if (!sendSms) return

        try {
            val smsMessage = buildMessagePayload(response)
            val postUrl = sms.apiUrl()
            val headers = addRequestHeaders()
            val entity = HttpEntity(smsMessage, headers)

            val smsResponse = restTemplate.postForEntity(postUrl, entity, MessageSendingResponse::class.java)
            val responseString = mapper.writerWithDefaultPrettyPrinter().writeValueAsString(smsResponse)
            logger.debug(responseString)
        } catch (ex: Exception) {
            logger.error("Sms sending error --> $ex", ex)
        }
    }

    private fun buildMessagePayload(response: RecommendationResponseDto): SmsMessage {
        val message = SmsMessage(
            user = sms.smsUser!!,
            password = sms.smsPass!!,
            gsm = response.mobileNumber!!
        )

        val brandedCodes = sms.brandedCodes
        if (brandedCodes == null || !brandedCodes.contains(response.mobileCountryCode)) {
            message.useDefaultSender = true
            logger.info("Sending SMS using default country service current request country code is ${response.mobileCountryCode}")
        } else {
            logger.info("Sending SMS using branded AKILIMO current request country code is ${response.mobileCountryCode}")
        }

        response.fertilizerRecText?.let { message.smsText = it }
        response.interCroppingRecText?.let { message.smsText = it }
        response.plantingPracticeRecText?.let { message.smsText = it }
        response.scheduledPlantingRecText?.let { message.smsText = it }

        return message
    }

    override fun sendEmailMessage(response: RecommendationResponseDto, email: Boolean) {
        if (!email) return
        logger.info("This feature for sending emails has not been implemented yet, please try again later ${response.userEmail}")
    }

    fun processPhoneNumber(recommendationResponseDto: RecommendationResponseDto): Phonenumber.PhoneNumber? {
        return phoneUtil.parse(recommendationResponseDto.mobileNumber, recommendationResponseDto.country)
    }

    fun convertToInternationalNumber(recommendationResponseDto: RecommendationResponseDto): String? {
        return processPhoneNumber(recommendationResponseDto)?.let { phoneUtil.format(it, PhoneNumberUtil.PhoneNumberFormat.E164) }
    }

    private fun addRequestHeaders(): HttpHeaders {
        return HttpHeaders().apply {
            contentType = MediaType.APPLICATION_JSON
        }
    }
}
