package com.acai.akilimo.interfaces


import com.acai.akilimo.entities.Recommendation

interface IRecommendationService {
    fun listAllRequests(): List<Recommendation>

    fun saveRecommendationRequest(recommendation: Recommendation): Recommendation?

    //fun sendToComputeTool(recommendation: Recommendation): RecommendationResponseDto?
}
