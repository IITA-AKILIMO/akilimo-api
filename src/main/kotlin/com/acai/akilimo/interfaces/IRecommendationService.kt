package com.acai.akilimo.interfaces


import com.acai.akilimo.entities.RecommendationRequest
import com.acai.akilimo.entities.RecommendationResponse
import com.acai.akilimo.mapper.RecommendationResponseDto

interface IRecommendationService {
    fun listAllRequests(): List<RecommendationRequest>

    fun saveRecommendationRequest(recommendationRequest: RecommendationRequest): RecommendationRequest?

    //fun sendToComputeTool(recommendationRequest: RecommendationRequest): RecommendationResponseDto?
}
