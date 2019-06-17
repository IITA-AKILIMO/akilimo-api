package com.acai.akilimo.request

import com.acai.akilimo.entities.ComputeRequest
import com.acai.akilimo.entities.FertilizerList
import com.fasterxml.jackson.annotation.JsonInclude
import com.fasterxml.jackson.annotation.JsonProperty
import lombok.Data

@Data

open class RecommendationRequest(
        @JsonProperty("computeValues")
        var computeRequest: ComputeRequest,

        @JsonProperty("fertilizerList")
        var fertilizerList: Set<FertilizerList>
)