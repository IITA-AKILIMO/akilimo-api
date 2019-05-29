package com.acai.akilimo.properties

import lombok.Data
import org.springframework.boot.context.properties.ConfigurationProperties
import org.springframework.stereotype.Component

import javax.validation.constraints.NotBlank

@Data
class Plumber {

    @NotBlank
    var baseUrl: String? = null

    @NotBlank
    var fertilizerRecommendation: String? = null

    @NotBlank
    var plantingPractices: String? = null

    @NotBlank
    var interCropping: String? = null

    @NotBlank
    var scheduledPlanting: String? = null

    @NotBlank
    var highStarch: String? = null
}
