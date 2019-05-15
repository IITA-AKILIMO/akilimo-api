package com.acai.akilimo.interfaces


import com.acai.akilimo.entities.RecommendationRequest

interface IRecommendationService {

    fun saveRecommendationRequest(recommendationRequest: RecommendationRequest): RecommendationRequest?

    fun findAll(): List<RecommendationRequest>
}
