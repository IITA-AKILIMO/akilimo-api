package com.iita.akilimo.core.interfaces

import com.iita.akilimo.core.response.RecommendationResponse
import com.iita.akilimo.database.entities.Recommendation


interface IRecommendationService {
    fun listAllRequests(): List<Recommendation>

    fun saveRecommendationRequest(recommendationResponse: Recommendation): Recommendation?

    //fun sendToComputeTool(recommendation: Recommendation): RecommendationResponseDto?
}
