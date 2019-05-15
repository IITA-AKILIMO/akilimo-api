package com.acai.akilimo.entities


import com.fasterxml.jackson.annotation.JsonIgnore
import com.fasterxml.jackson.annotation.JsonInclude
import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder
import lombok.Data

import java.util.HashMap

@Data
@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonPropertyOrder("lat", "lon", "rateUrea", "rateNPK151515", "rateNPK201010", "currentY", "targetY", "WLY", "netRev", "totalCost", "N", "P", "K", "plDate", "areaUnits", "area", "rateUrea_user", "rateNPK151515_user", "rateNPK201010_user", "currentY_user", "targetY_user", "WLY_user", "netRev_user", "totalCost_user", "N_user", "P_user", "K_user")
class RecommendationResponse {

    @JsonProperty("lat")
    var lat: Double? = null
        set(lat) {
            field = this.lat
        }
    @JsonProperty("lon")
    var lon: Double? = null
        set(lon) {
            field = this.lon
        }
    @JsonProperty("rateUrea")
    var rateUrea: Double? = null
        set(rateUrea) {
            field = this.rateUrea
        }
    @JsonProperty("rateNPK151515")
    var rateNPK151515: Double? = null
        set(rateNPK151515) {
            field = this.rateNPK151515
        }
    @JsonProperty("rateNPK201010")
    var rateNPK201010: Double? = null
        set(rateNPK201010) {
            field = this.rateNPK201010
        }
    @JsonProperty("currentY")
    var currentY: Double? = null
        set(currentY) {
            field = this.currentY
        }
    @JsonProperty("targetY")
    var targetY: Double? = null
        set(targetY) {
            field = this.targetY
        }
    @JsonProperty("WLY")
    var wly: Double? = null
        set(wLY) {
            field = wly
        }
    @JsonProperty("netRev")
    var netRev: Int? = null
        set(netRev) {
            field = this.netRev
        }
    @JsonProperty("totalCost")
    var totalCost: Int? = null
        set(totalCost) {
            field = this.totalCost
        }
    @JsonProperty("N")
    var n: Int? = null
        set(n) {
            field = this.n
        }
    @JsonProperty("P")
    var p: Int? = null
        set(p) {
            field = this.p
        }
    @JsonProperty("K")
    var k: Int? = null
        set(k) {
            field = this.k
        }
    @JsonProperty("plDate")
    var plDate: Int? = null
        set(plDate) {
            field = this.plDate
        }
    @JsonProperty("areaUnits")
    var areaUnits: String? = null
        set(areaUnits) {
            field = this.areaUnits
        }
    @JsonProperty("area")
    var area: Double? = null
        set(area) {
            field = this.area
        }
    @JsonProperty("rateUrea_user")
    var rateUreaUser: Double? = null
        set(rateUreaUser) {
            field = this.rateUreaUser
        }
    @JsonProperty("rateNPK151515_user")
    var rateNPK151515User: Double? = null
        set(rateNPK151515User) {
            field = this.rateNPK151515User
        }
    @JsonProperty("rateNPK201010_user")
    var rateNPK201010User: Double? = null
        set(rateNPK201010User) {
            field = this.rateNPK201010User
        }
    @JsonProperty("currentY_user")
    var currentYUser: Double? = null
        set(currentYUser) {
            field = this.currentYUser
        }
    @JsonProperty("targetY_user")
    var targetYUser: Double? = null
        set(targetYUser) {
            field = this.targetYUser
        }
    @JsonProperty("WLY_user")
    var wlyUser: Double? = null
        set(wLYUser) {
            field = wlyUser
        }
    @JsonProperty("netRev_user")
    var netRevUser: Int? = null

    @JsonProperty("totalCost_user")
    var totalCostUser: Int? = null

    @JsonProperty("N_user")
    var nUser: Double? = null

    @JsonProperty("P_user")
    var pUser: Double? = null

    @JsonProperty("K_user")
    var kUser: Double? = null

    @JsonProperty("recommendation")
    var reccomendationText: String? = null
    @JsonIgnore
    var additionalProperties: Map<String, Any> = HashMap()


}