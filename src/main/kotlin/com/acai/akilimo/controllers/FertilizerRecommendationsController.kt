package com.acai.akilimo.controllers


import com.acai.akilimo.entities.RecommendationRequest
import com.acai.akilimo.mapper.RecommendationRequestDto
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.request.ComputeRequest
import com.acai.akilimo.service.RecommendationServiceImp
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
constructor(private val recommendationServiceImp: RecommendationServiceImp) {

    private val logger = LoggerFactory.getLogger(FertilizerRecommendationsController::class.java)


    //@GetMapping("/fertilizer/requests")
    fun request(): List<RecommendationRequest> {
        return recommendationServiceImp.listAllRequests()
    }

    //@PostMapping("/fertilizer")
    fun request(@RequestBody recommendationRequest: RecommendationRequestDto): ResponseEntity<RecommendationResponseDto> {
        val modelMapper = ModelMapper()
        var recommendationResponseDto: RecommendationResponseDto? = null

        val request = modelMapper.map(recommendationRequest, RecommendationRequest::class.java)

        val response = recommendationServiceImp.saveRecommendationRequest(request!!)

        return when {
            response != null -> {
                logger.info("Returning a response after logging")
                recommendationResponseDto = modelMapper.map(response, RecommendationResponseDto::class.java)

                ResponseEntity(recommendationResponseDto, HttpStatus.OK)
            }
            else -> ResponseEntity(recommendationResponseDto, HttpStatus.NOT_FOUND)
        }

    }

    @PostMapping("/fertilizer-test")
    fun requestTest(@RequestBody computeRequest: ComputeRequest): ResponseEntity<RecommendationResponseDto> {
        val modelMapper = ModelMapper()
        var recommendationResponseDto: RecommendationResponseDto? = null

        //val request = modelMapper.map(computeRequest, RecommendationRequest::class.java)

        //val response = recommendationServiceImp.saveRecommendationRequest(request!!)
        val response = recommendationServiceImp.computeRecommendations(computeRequest)

        return when {
            response != null -> {
                recommendationResponseDto = modelMapper.map(response, RecommendationResponseDto::class.java)
                ResponseEntity(recommendationResponseDto, HttpStatus.OK)
            }
            else -> ResponseEntity(recommendationResponseDto, HttpStatus.NOT_FOUND)
        }

    }
}
