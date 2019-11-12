package com.acai.akilimo.service

import com.acai.akilimo.entities.Recommendation
import com.acai.akilimo.repositories.RecommendationRepository
import com.acai.akilimo.interfaces.IRecommendationService
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service

@Service
class RecommendationServiceImp @Autowired
constructor(private val recommendationRepository: RecommendationRepository) : IRecommendationService {
    private val logger = LoggerFactory.getLogger(IRecommendationService::class.java)


    override fun listAllRequests(): List<Recommendation> {
        return recommendationRepository.findAll()
    }

    override fun saveRecommendationRequest(recommendation: Recommendation): Recommendation? {
        try {
            val fertilizerList = recommendation.addFertilizers(recommendation)

            recommendation.fertilizers = fertilizerList

            logger.info("Logging requests for recommendations", 6)
            return recommendationRepository.save(recommendation)
        } catch (ex: Exception) {
            logger.error(ex.message)
        }

        return null
    }
}
