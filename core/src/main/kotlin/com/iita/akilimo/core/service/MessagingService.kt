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
class MessagingService
constructor(val akilimoConfig: AkilimoConfigProperties) : IMessagingService {

    private val logger = LoggerFactory.getLogger(MessagingService::class.java)
    private val sms: MessagingProperties = akilimoConfig.sms()

    private val phoneUtil = PhoneNumberUtil.getInstance()

    private val mapper = ObjectMapper()
    private val restTemplate = RestTemplate()

    override fun sendTextMessage(response: RecommendationResponseDto, sendSms: Boolean) {
        if (!sendSms) {
            return
        }
        try {
            val smsMessage = buildMessagePayload(response)
            val postUrl = sms.apiUrl()
            val headers = addRequestHeaders()
            val entity = HttpEntity(smsMessage, headers)

            val response = restTemplate.postForEntity(postUrl, entity, MessageSendingResponse::class.java)
            val responseString = mapper.writerWithDefaultPrettyPrinter().writeValueAsString(response)
            logger.info(responseString)
        } catch (ex: Exception) {
            logger.info(ex.message)
            logger.error(ex.message, ex)
        }
    }

    private fun buildMessagePayload(response: RecommendationResponseDto): SmsMessage {

        val message = SmsMessage(
            user = sms.smsUser!!,
            password = sms.smsPass!!,
            gsm = response.mobileNumber!!
        )

        val brandeCodes = sms.brandedCodes

        if (brandeCodes != null) {
            if (!brandeCodes.contains(response.mobileCountryCode)) {
                message.useDefaultSender = true
                logger.info("Sending SMS using default country service current request country code is ${response.mobileCountryCode}")
            } else {
                logger.info("Sending SMS using branded AKILIMO current request country code is ${response.mobileCountryCode}")
            }
        } else {
            logger.info("Sending SMS using branded AKILIMO current request country code is ${response.mobileCountryCode}")
        }


        when {
            !response.fertilizerRecText.isNullOrEmpty() -> {
                message.smsText = response.fertilizerRecText
            }
        }

        when {
            !response.interCroppingRecText.isNullOrEmpty() -> {
                message.smsText = response.interCroppingRecText
            }
        }

        when {
            !response.plantingPracticeRecText.isNullOrEmpty() -> {
                message.smsText = response.plantingPracticeRecText
            }
        }

        when {
            !response.scheduledPlantingRecText.isNullOrEmpty() -> {
                message.smsText = response.scheduledPlantingRecText
            }
        }
        return message
    }

    override fun sendEmailMessage(response: RecommendationResponseDto, email: Boolean) {
        if (!email) {
            return
        }
        logger.info("This feature for sending emails has not been implemented yet, please try again later ${response.userEmail}")
    }

    fun processPhoneNumber(recommendationResponseDto: RecommendationResponseDto): Phonenumber.PhoneNumber? {
        val countryCode = recommendationResponseDto.country
        val phoneNumber = recommendationResponseDto.mobileNumber?.toLong()

        return phoneUtil.parse(phoneNumber.toString(), countryCode)
    }

    fun convertToInternationalNumber(recommendationResponseDto: RecommendationResponseDto): String? {

        val phoneNumber = processPhoneNumber(recommendationResponseDto)

        return phoneUtil.format(phoneNumber, PhoneNumberUtil.PhoneNumberFormat.E164)
    }

    private fun addRequestHeaders(): HttpHeaders {
        val headers = HttpHeaders()

        headers.contentType = MediaType.APPLICATION_JSON

        return headers
    }
}
