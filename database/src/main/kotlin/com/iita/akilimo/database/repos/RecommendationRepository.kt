package com.iita.akilimo.database.repos

import com.iita.akilimo.database.entities.Recommendation
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.stereotype.Repository

@Repository
interface RecommendationRepository : JpaRepository<Recommendation, Long>{
}
