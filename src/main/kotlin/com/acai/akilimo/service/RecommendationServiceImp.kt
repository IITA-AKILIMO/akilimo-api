package com.acai.akilimo.service

import com.acai.akilimo.entities.RecommendationRequest
import com.acai.akilimo.repositories.RecommendationRepository
import com.acai.akilimo.interfaces.IRecommendationService
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.stereotype.Service

@Service
class RecommendationServiceImp @Autowired
constructor(private val recommendationRepository: RecommendationRepository) : IRecommendationService {

    override fun saveRecommendationRequest(recommendationRequest: RecommendationRequest): RecommendationRequest? {
        try {
            val fertilizerList = recommendationRequest.addFertilizers(recommendationRequest)

            recommendationRequest.fertilizers = fertilizerList

            return recommendationRepository.save(recommendationRequest)
        } catch (ex: Exception) {
            ex.printStackTrace()
        }

        return null
    }

    override fun findAll(): List<RecommendationRequest> {
        return recommendationRepository.findAll()
    }
}
