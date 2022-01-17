package com.iita.akilimo.config

import org.apache.commons.logging.impl.NoOpLog
import javax.validation.constraints.NotBlank

class RecommendationProperties {
    @NotBlank
    var username: String? = null

    @NotBlank
    var userField: String? = null

    @NotBlank
    var userPhoneCode: String? = null

    @NotBlank
    var userPhoneNumber: String? = null

    @NotBlank
    var userEmail: String? = null

    @NotBlank
    var cmp: Int? = null

    @NotBlank
    var fcy: Int? = null

    @NotBlank
    var riskAtt: Int? = null

    @NotBlank
    var pdWindow: Int? = null

    @NotBlank
    var hdWindow: Int? = null

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
    var nameSf: String? = null

    @NotBlank
    var saleSf: Boolean? = null

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
    var cassPd: String? = null

    @NotBlank
    var cassUpM1: Double? = null

    @NotBlank
    var cassUpM2: Double? = null

    @NotBlank
    var cassUpP1: Double? = null

    @NotBlank
    var cassUpP2: Double? = null

    @NotBlank
    var sweetPotatoUw: Double? = null

    @NotBlank
    var sweetPotatoUp: Double? = null

    @NotBlank
    var cassUp: Double? = null

    @NotBlank
    var cassUw: Double? = null

    @NotBlank
    var maizeUp: Double? = null

    @NotBlank
    var maizeUw: Double? = null

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
    var costTractorPRidging: Double? = null

    @NotBlank
    var costWeeding1: Double? = null

    @NotBlank
    var costWeeding2: Double? = null

    @NotBlank
    var maxInv: Double? = null
}
