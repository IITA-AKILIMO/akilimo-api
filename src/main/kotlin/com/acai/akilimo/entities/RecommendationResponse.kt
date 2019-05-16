package com.acai.akilimo.entities


import com.fasterxml.jackson.annotation.*
import lombok.Data

import java.util.HashMap

@Data
@JsonIgnoreProperties(ignoreUnknown = true)
@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonPropertyOrder("lat", "lon", "rateUrea", "rateNPK151515", "rateNPK201010", "currentY", "targetY", "WLY", "netRev", "totalCost", "N", "P", "K", "plDate", "areaUnits", "area", "rateUrea_user", "rateNPK151515_user", "rateNPK201010_user", "currentY_user", "targetY_user", "WLY_user", "netRev_user", "totalCost_user", "N_user", "P_user", "K_user")
class RecommendationResponse {

    @JsonProperty("lat")
    var mapLat: Float? = null

    @JsonProperty("lon")
    var mapLong: Float? = null

    @JsonProperty("rateUrea")
    var rateUrea: Float? = null

    @JsonProperty("rateNPK151515")
    var rateNPK151515: Float? = null

    @JsonProperty("rateNPK201010")
    var rateNPK201010: Float? = null

    @JsonProperty("currentY")
    var currentieldY: Float? = null

    @JsonProperty("targetY")
    var targetYield: Float? = null

    @JsonProperty("WLY")
    var wly: Float? = null

    @JsonProperty("netRev")
    var netRevenue: Int? = null

    @JsonProperty("totalCost")
    var totalCost: Int? = null

    @JsonProperty("N")
    var nitrogenAmount: Int? = null

    @JsonProperty("P")
    var phospateAmount: Int? = null

    @JsonProperty("K")
    var potassiumAmount: Int? = null

    @JsonProperty("plDate")
    var plantingDate: Int? = null

    @JsonProperty("areaUnits")
    var areaUnits: String? = null

    @JsonProperty("area")
    var area: Float? = null

    @JsonProperty("rateUrea_user")
    var rateUreaUser: Float? = null

    @JsonProperty("rateNPK151515_user")
    var rateNPK151515User: Float? = null

    @JsonProperty("rateNPK201010_user")
    var rateNPK201010User: Float? = null

    @JsonProperty("currentY_user")
    var currentYUser: Float? = null

    @JsonProperty("targetY_user")
    var targetYUser: Float? = null

    @JsonProperty("WLY_user")
    var wlyUser: Float? = null

    @JsonProperty("netRev_user")
    var netRevUser: Int? = null

    @JsonProperty("totalCost_user")
    var totalCostUser: Int? = null

    @JsonProperty("N_user")
    var nUser: Float? = null

    @JsonProperty("P_user")
    var pUser: Float? = null

    @JsonProperty("K_user")
    var kUser: Float? = null

    @JsonProperty("recommendation")
    var recommendationText: String? = null


}