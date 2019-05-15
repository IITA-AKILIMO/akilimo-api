package com.acai.akilimo.repositories;

import com.acai.akilimo.entities.YieldRequest;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface YieldRequestRepository extends JpaRepository<YieldRequest, Long> {
}
