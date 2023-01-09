package com.iita.akilimo.config

import com.fasterxml.jackson.annotation.JsonProperty
import org.apache.commons.logging.impl.NoOpLog
import org.springframework.beans.factory.annotation.Value
import javax.validation.constraints.NotBlank

class RecommendationProperties {
    @NotBlank
    var username: String? = null

    @NotBlank
    var userField: String? = null

    @NotBlank
    var areaUnits: String? = null

    @NotBlank
    var userPhoneCode: String? = null

    @NotBlank
    var userPhoneNumber: String? = null

    @NotBlank
    var userEmail: String? = null

    @NotBlank
    var currentMaizePerformance: Int? = null

    @NotBlank
    var currentFieldYield: Int? = null

    @NotBlank
    var riskAttitude: Int? = null

    @NotBlank
    var plantingDateWindow: Int? = null

    @NotBlank
    var harvestDateWindow: Int? = null

    @NotBlank
    var fallowType: String? = null

    @NotBlank
    var fallowHeight: Int? = null

    @NotBlank
    var fallowGreen: Boolean? = null

    @NotBlank
    var problemWeeds: Boolean? = null

    @NotBlank
    var tractorPlough: Boolean? = null

    @NotBlank
    var sms: Boolean? = null

    @NotBlank
    var email: Boolean? = null

    @NotBlank
    var tractorHarrow: Boolean? = null

    @NotBlank
    var tractorRidger: Boolean? = null

    @NotBlank
    var starchFactoryName: String? = null

    @NotBlank
    var sellToStarchFactory: Boolean? = null

    @NotBlank
    var ploughing: Boolean? = null

    @NotBlank
    var ridging: Boolean? = null

    @NotBlank
    var methodPloughing: String? = null

    @NotBlank
    var methodRidging: String? = null

    @NotBlank
    var methodHarrowing: String? = null

    @NotBlank
    var costLmoAreaBasis: String? = null

    @NotBlank
    var cassavaProduceType: String? = null

    @NotBlank
    var cassUpM1: Double? = null

    @NotBlank
    var cassUpM2: Double? = null

    @NotBlank
    var cassUpP1: Double? = null

    @NotBlank
    var cassUpP2: Double? = null

    @NotBlank
    var sweetPotatoProduceType: String? = null

    @NotBlank
    var sweetPotatoUnitWeight: Double? = null

    @NotBlank
    var sweetPotatoUnitPrice: Double? = null

    @NotBlank
    var cassavaUnitPrice: Double? = null

    @NotBlank
    var cassavaUnitWeight: Double? = null

    @NotBlank
    var maizeProduceType: String? = null


    @NotBlank
    var maizeUnitPrice: Double? = null

    @NotBlank
    var maizeUnitWeight: Double? = null

    @NotBlank
    var costManualPloughing: Double? = null

    @NotBlank
    var costManualHarrowing: Double? = null

    @NotBlank
    var costManualRidging: Double? = null

    @NotBlank
    var costTractorPloughing: Double? = null

    @NotBlank
    var costTractorHarrowing: Double? = null

    @NotBlank
    var costTractorRidging: Double? = null

    @NotBlank
    var costWeeding1: Double? = null

    @NotBlank
    var costWeeding2: Double? = null

    @NotBlank
    var maxInvestment: Double? = null
}
