package com.acai.akilimo.controllers


import com.acai.akilimo.entities.RecommendationRequest
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.request.ComputeRequest
import com.acai.akilimo.service.MessagingService
import com.acai.akilimo.service.RecommendationService
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import org.springframework.http.HttpStatus


@RequestMapping("/api/v2/recommendations")
@RestController
class FertilizerRecommendationsController
@Autowired
constructor(private val recommendationService: RecommendationService, private val messagingService: MessagingService) {

    private val logger = LoggerFactory.getLogger(FertilizerRecommendationsController::class.java)


    //@GetMapping("/fertilizer/requests")
    fun request(): List<RecommendationRequest> {
        return recommendationService.listAllRequests()
    }

    //@PostMapping("/fertilizer")
    /*fun request(@RequestBody recommendationRequest: RecommendationRequestDto): ResponseEntity<RecommendationResponseDto> {
        val modelMapper = ModelMapper()
        var recommendationResponseDto: RecommendationResponseDto? = null

        val request = modelMapper.map(recommendationRequest, RecommendationRequest::class.java)

        val response = recommendationService.saveRecommendationRequest(request!!)

        return when {
            response != null -> {
                logger.info("Returning a response after logging")
                recommendationResponseDto = modelMapper.map(response, RecommendationResponseDto::class.java)

                ResponseEntity(recommendationResponseDto, HttpStatus.OK)
            }
            else -> ResponseEntity(recommendationResponseDto, HttpStatus.NOT_FOUND)
        }

    }*/

    @PostMapping("/fertilizer-test")
    fun requestTest(@RequestBody computeRequest: ComputeRequest): ResponseEntity<RecommendationResponseDto> {
        val modelMapper = ModelMapper()
        var recommendationResponseDto: RecommendationResponseDto? = null

        //val request = modelMapper.map(computeRequest, RecommendationRequest::class.java)
        //val response = recommendationService.saveRecommendationRequest(request!!)
        val response = recommendationService.computeRecommendations(computeRequest)

        return when {
            response != null -> {
                //send sms
                if (computeRequest.sendSms) {
                    if (response.hasResponse) {
                        val resp = messagingService.sendTextMessage(response)
                        logger.info("Sms sending response ${resp.toString()}")
                    } else {
                        logger.info("There are no responses for sending the sms")
                    }
                }

                if (computeRequest.email) {
                    val resp = messagingService.sendEmailMessage(response)
                    logger.info("email sending response ${resp.toString()}")
                }
                recommendationResponseDto = modelMapper.map(response, RecommendationResponseDto::class.java)
                ResponseEntity(recommendationResponseDto, HttpStatus.OK)
            }
            else -> ResponseEntity(recommendationResponseDto, HttpStatus.EXPECTATION_FAILED)
        }

    }
}
