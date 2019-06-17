package com.acai.akilimo.entities


import com.fasterxml.jackson.annotation.*
import lombok.Data

import java.util.HashMap
import com.fasterxml.jackson.annotation.JsonProperty



@Suppress("unused")
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
class Response {
    @JsonProperty("name")
    var userName: String? = null

    @JsonProperty("phone")
    var phone: String? = null

    @JsonProperty("email")
    var email: String? = null

    @JsonProperty("field")
    var fieldDescription: String? = null

    @JsonProperty("field_area")
    var fieldArea: String? = null

    @JsonProperty("unit_field")
    var fieldSizeUnit: String? = null

    @JsonProperty("plant_date")
    var plantingDate: String? = null

    @JsonProperty("hvst_date")
    var harvestDate: String? = null

    @JsonProperty("current_yield")
    var currentYield: String? = null


    @JsonProperty("latitude")
    var mapLat: Float? = null

    @JsonProperty("longitude")
    var mapLong: Float? = null

    @JsonProperty("currency")
    var currency: String? = null

    @JsonProperty("fertilizer1")
    var fertilizerOne: String? = null

    @JsonProperty("cost1")
    var fertilizerOnecost: Float? = null

    @JsonProperty("unit1")
    var fertilizerOneUnit: String? = null

    @JsonProperty("kgs1")
    var fertilizerOneKillo: Float? = null

    @JsonProperty("fertilizer2")
    var fertilizerTwo: String? = null

    @JsonProperty("cost2")
    var fertilizerTwoCost: Float? = null

    @JsonProperty("unit2")
    var fertilizerTwoUnit: String? = null

    @JsonProperty("kgs2")
    var fertilizerTwoKilo: Float? = null

    @JsonProperty("fertilizer3")
    var fertilizerThree: String? = null

    @JsonProperty("cost3")
    var fertilizerThreeCost: Float? = null

    @JsonProperty("unit3")
    var fertilizerThreeUnit: String? = null

    @JsonProperty("kgs3")
    var fertilizerThreeKilo: Long? = null

    @JsonProperty("fertilizer4")
    var fertilizerFour: String? = null

    @JsonProperty("costcassava")
    var cassavaCost: Float? = null

    @JsonProperty("unitcassava")
    var cassavaUnit: String? = null

    @JsonProperty("maxinvest")
    var maximumInvestment: Float? = null

    @JsonProperty("bags1")
    var bagsOne: Float? = null
    @JsonProperty("bags2")
    var bagsTwo: Long? = null

    @JsonProperty("total_cost1")
    var totalCostOne: Float? = null
    @JsonProperty("total_cost2")
    var totalCostTwo: Float? = null


    @JsonProperty("sum_total")
    var sumTotal: Float? = null

    @JsonProperty("bags_total")
    var bagsTotal: Float? = null

    @JsonProperty("product")
    var cassavaProduct: String? = null

    @JsonProperty("unit")
    var weightunit: String? = null

    @JsonProperty("totalSalePrice")
    var totalSalePrice: Float? = null

    @JsonProperty("revenue")
    var totalRevenue: Float? = null
}