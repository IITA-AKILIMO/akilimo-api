package com.iita.akilimo.core.service

import com.iita.akilimo.core.response.RecommendationResponse
import com.iita.akilimo.database.entities.Recommendation
import com.iita.akilimo.database.repos.RecommendationRepository
import org.slf4j.LoggerFactory
import org.springframework.stereotype.Service
import com.iita.akilimo.core.interfaces.IRecommendationService as IRecommendationService1

@Service
class RecommendationServiceImp
constructor(val recommendationRepository: RecommendationRepository) : IRecommendationService1 {
    private val logger = LoggerFactory.getLogger(IRecommendationService1::class.java)


    override fun listAllRequests(): List<Recommendation> {
        return recommendationRepository.findAll()
    }

    override fun saveRecommendationRequest(recommendationResponse: Recommendation): Recommendation? {
        try {
            val fertilizerList = recommendationResponse.addFertilizers(recommendationResponse)

            recommendationResponse.fertilizers = fertilizerList

            logger.info("Logging requests for recommendations", fertilizerList.size)

            return recommendationRepository.save(recommendationResponse)
        } catch (ex: Exception) {
            logger.error(ex.message)
        }

        return null
    }
}
