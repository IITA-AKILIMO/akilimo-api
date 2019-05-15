package com.acai.akilimo.service

import com.acai.akilimo.controllers.RecommendationsController
import com.acai.akilimo.entities.RecommendationRequest
import com.acai.akilimo.repositories.RecommendationRepository
import com.acai.akilimo.interfaces.IRecommendationService
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service

@Service
class RecommendationServiceImp @Autowired
constructor(private val recommendationRepository: RecommendationRepository) : IRecommendationService {

    private val logger = LoggerFactory.getLogger(IRecommendationService::class.java)

    override fun saveRecommendationRequest(recommendationRequest: RecommendationRequest): RecommendationRequest? {
        try {
            val fertilizerList = recommendationRequest.addFertilizers(recommendationRequest)

            recommendationRequest.fertilizers = fertilizerList

            logger.info("Logging requests for recommendations ")
            return recommendationRepository.save(recommendationRequest)
        } catch (ex: Exception) {
            logger.error(ex.message,ex)
        }

        return null
    }

    override fun findAll(): List<RecommendationRequest> {
        return recommendationRepository.findAll()
    }
}
