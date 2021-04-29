package com.iita.akilimo.core.interfaces


import com.iita.akilimo.database.repos.Recommendation

interface IRecommendationService {
    fun listAllRequests(): List<Recommendation>

    fun saveRecommendationRequest(recommendation: Recommendation): Recommendation?

    //fun sendToComputeTool(recommendation: Recommendation): RecommendationResponseDto?
}
