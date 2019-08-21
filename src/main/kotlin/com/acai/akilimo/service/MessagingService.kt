package com.acai.akilimo.service

import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.interfaces.IMessagingService
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.properties.MessagingProperties
import com.google.i18n.phonenumbers.Phonenumber
import infobip.api.config.BasicAuthConfiguration
import java.util.*
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import com.google.i18n.phonenumbers.PhoneNumberUtil
import infobip.api.model.Destination
import infobip.api.model.sms.mt.send.Message
import infobip.api.model.sms.mt.send.textual.SMSAdvancedTextualRequest
import infobip.api.client.SendMultipleTextualSmsAdvanced
import org.slf4j.LoggerFactory
import kotlin.collections.ArrayList


@Service
class MessagingService
@Autowired
constructor(akilimoConfigProperties: AkilimoConfigProperties) : IMessagingService {

    private val logger = LoggerFactory.getLogger(MessagingService::class.java)
    private val infobipSms: MessagingProperties = akilimoConfigProperties.infoBipSms()
    private val globalParams: MessagingProperties = akilimoConfigProperties.globalProperties()
    private val phoneUtil = PhoneNumberUtil.getInstance()
    /* override fun sendTextMessage() {
         val client = PlivoClient(plivo.authId, plivo.authKey)

         Message.creator("254727876796", Collections.singletonList("254713196504"), "Hello, world!")
                 .client(client)
                 .create()
     }*/

    override fun sendTextMessage(response: RecommendationResponseDto, sendSms: Boolean) {
        if (!sendSms) {
            return
        }
        val basicAuthConfiguration = BasicAuthConfiguration(infobipSms.userName, infobipSms.userPass)
        val messageList = buildMessagePayload(response)
        val client = SendMultipleTextualSmsAdvanced(basicAuthConfiguration)

        val requestBody = SMSAdvancedTextualRequest()
        if (messageList.size > 0) {
            //requestBody.messages = Collections.singletonList(message)
            requestBody.messages = messageList
            client.execute(requestBody)
        }
    }

    private fun buildMessagePayload(response: RecommendationResponseDto): ArrayList<Message> {
        val messageList = arrayListOf<Message>()
        val webHookUrl = globalParams.webHookUrl
        val destination = Destination()
        destination.to = response.fullPhoneNumber

        val message = Message()
        message.from = infobipSms.sender
        message.destinations = Collections.singletonList(destination)
        message.notifyUrl = webHookUrl
        if (response.fertilizerRecText != null) {
            message.text = response.fertilizerRecText
            messageList.add(message)
        }

        if (response.interCroppingRecText != null) {
            message.text = response.interCroppingRecText
            messageList.add(message)
        }

        if (response.plantingPracticeRecText != null) {
            message.text = response.plantingPracticeRecText
            messageList.add(message)
        }

        if (response.scheduledPlantingRecText != null) {
            message.text = response.scheduledPlantingRecText
            messageList.add(message)
        }
        return messageList
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
}