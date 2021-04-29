package com.iita.akilimo.database.repos

import com.acai.akilimo.entities.Recommendation
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface RecommendationRepository : JpaRepository<Recommendation, Long>{
}
