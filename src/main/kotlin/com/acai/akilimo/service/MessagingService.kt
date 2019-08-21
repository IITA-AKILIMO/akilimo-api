package com.acai.akilimo.service

import com.acai.akilimo.config.AkilimoConfigProperties
import com.acai.akilimo.interfaces.IMessagingService
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.properties.MessagingProperties
import com.google.i18n.phonenumbers.Phonenumber
import infobip.api.client.SendSingleTextualSms
import infobip.api.config.BasicAuthConfiguration
import java.util.*
import infobip.api.model.sms.mt.send.textual.SMSTextualRequest
import infobip.api.model.sms.mt.send.SMSResponse
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service
import com.google.i18n.phonenumbers.PhoneNumberUtil
import infobip.api.model.Destination
import infobip.api.model.sms.mt.send.Message
import retrofit.RetrofitError
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

        if (response.fertilizerRecText != null) {
            val message = Message()
            message.from = infobipSms.sender
            message.destinations = Collections.singletonList(destination)
            message.text = response.fertilizerRecText
            message.notifyUrl = webHookUrl
            messageList.add(message)
        }

        if (response.interCroppingRecText != null) {
            val message = Message()
            message.from = infobipSms.sender
            message.destinations = Collections.singletonList(destination)
            message.text = response.interCroppingRecText
            message.notifyUrl = webHookUrl
            messageList.add(message)
        }

        if (response.plantingPracticeRecText != null) {
            val message = Message()
            message.from = infobipSms.sender
            message.destinations = Collections.singletonList(destination)
            message.text = response.plantingPracticeRecText
            message.notifyUrl = webHookUrl
            messageList.add(message)
        }

        if (response.scheduledPlantingRecText != null) {
            val message = Message()
            message.from = infobipSms.sender
            message.destinations = Collections.singletonList(destination)
            message.text = response.scheduledPlantingRecText
            message.notifyUrl = webHookUrl
            messageList.add(message)
        }
        return messageList
    }

    fun sendTextMessageOld(response: RecommendationResponseDto): SMSResponse? {
        val auth = BasicAuthConfiguration(infobipSms.userName, infobipSms.userPass)

        val message = response.fertilizerRecText;
        val client = SendSingleTextualSms(auth)
        val phoneNumber = processPhoneNumber(response)

        val isValid = phoneUtil.isValidNumber(phoneNumber) // returns true
        if (isValid) {
            val phoneNumberString = convertToInternationalNumber(response)
            val requestBody = SMSTextualRequest()
            requestBody.from = infobipSms.sender
            requestBody.to = Arrays.asList(phoneNumberString)
            requestBody.text = message

            return try {
                client.execute(requestBody)
            } catch (ex: RetrofitError) {
                logger.error(ex.message)
                return null
            }
        }
        logger.info("This phone number appears to be invalid ${response.mobileNumber}")
        return null
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

        val swissNumberProto = processPhoneNumber(recommendationResponseDto)

        return phoneUtil.format(swissNumberProto, PhoneNumberUtil.PhoneNumberFormat.E164)
    }
}