package com.acai.akilimo.controllers


import com.acai.akilimo.entities.RecommendationRequest
import com.acai.akilimo.mapper.RecommendationRequestDto
import com.acai.akilimo.mapper.RecommendationResponseDto
import com.acai.akilimo.service.RecommendationServiceImp
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.web.bind.annotation.*


@RequestMapping("/api/v2/recommendations")
@RestController
class FertilizerRecommendationsController
@Autowired
constructor(private val recommendationServiceImp: RecommendationServiceImp) {

    private val logger = LoggerFactory.getLogger(FertilizerRecommendationsController::class.java)


    @GetMapping("/fertilizer/requests")
    fun request(): List<RecommendationRequest> {
        return recommendationServiceImp.listAllRequests()
    }

    @PostMapping("/fertilizer")
    fun request(@RequestBody recommendationRequest: RecommendationRequestDto): RecommendationResponseDto? {
        val modelMapper = ModelMapper()

        val request = modelMapper.map(recommendationRequest, RecommendationRequest::class.java)

        val response = recommendationServiceImp.saveRecommendationRequest(request!!)
        if (response != null) {
            logger.info("Returning a response after logging")
            return modelMapper.map(response, RecommendationResponseDto::class.java)
        }

        return null
    }
}
