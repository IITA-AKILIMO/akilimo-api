package com.acai.akilimo.service

import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.interfaces.IMessagingService
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.properties.MessagingProperties
import com.acai.akilimo.request.SmsMessage
import com.acai.akilimo.response.MessageSendingResponse
import com.fasterxml.jackson.databind.ObjectMapper
import com.google.common.base.Strings
import com.google.i18n.phonenumbers.PhoneNumberUtil
import com.google.i18n.phonenumbers.Phonenumber
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.http.HttpEntity
import org.springframework.http.HttpHeaders
import org.springframework.http.MediaType
import org.springframework.http.ResponseEntity
import org.springframework.stereotype.Service
import org.springframework.web.client.RestTemplate


@Service
class MessagingService
@Autowired

constructor(final val akilimoConfig: AkilimoConfigProperties) : IMessagingService {

    private val logger = LoggerFactory.getLogger(MessagingService::class.java)
    private val sms: MessagingProperties = akilimoConfig.sms()

    private val phoneUtil = PhoneNumberUtil.getInstance()

    private val mapper = ObjectMapper()
    private val restTemplate = RestTemplate()

    @Suppress("NAME_SHADOWING")
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
            logger.error(ex.message)
        }
    }

    private fun buildMessagePayload(response: RecommendationResponseDto): SmsMessage {

        val message = SmsMessage(userName = sms.smsUser, password = sms.smsToken)
        message.mobileNumber = response.mobileNumber

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

        val fertRectText = response.fertilizerRecText!!

        if (!fertRectText.contains("Hatuna mapendekezo yoyote")
                || !fertRectText.contains("We do not have fertilizer recommendation for your location")
                && !Strings.isNullOrEmpty(response.fertilizerRecText)) {
            message.smsText = response.fertilizerRecText
        }


        when {
            !Strings.isNullOrEmpty(response.interCroppingRecText) -> {
                message.smsText = response.interCroppingRecText
            }
        }

        when {
            !Strings.isNullOrEmpty(response.plantingPracticeRecText) -> {
                message.smsText = response.plantingPracticeRecText
            }
        }

        when {
            !Strings.isNullOrEmpty(response.scheduledPlantingRecText) -> {
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