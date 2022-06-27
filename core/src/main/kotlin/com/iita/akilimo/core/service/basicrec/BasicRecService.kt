package com.iita.akilimo.core.service.basicrec

import com.iita.akilimo.core.request.PlumberComputeRequest
import com.iita.akilimo.core.request.usecases.fr.BasicFrRequest
import com.iita.akilimo.core.service.RecommendationService
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Value
import org.springframework.stereotype.Service
import org.springframework.web.client.RestTemplate
import java.time.LocalDate

@Service
class BasicRecService(
    private val recService: RecommendationService,
    val restTemplate: RestTemplate,
    @Value("\${akilimo.recommendation.default-url}") private val defaultUrl: String
) {
    private val logger = LoggerFactory.getLogger(this::class.java)
    private val modelMapper = ModelMapper()

    fun computeFrRecommendation(frRequest: BasicFrRequest): PlumberComputeRequest {
        //assign default values now
        val resp = fetchDefaultValues(frRequest.country)
        resp.fertilizerRec = true
        resp.country = frRequest.country
        resp.currentFieldYield = frRequest.currentFieldYield
        resp.fieldArea = frRequest.area
        resp.areaUnits = frRequest.areaUnit
        resp.mapLat = frRequest.lat
        resp.mapLong = frRequest.lon

        //compute the planting dates
        val currentDate = LocalDate.of(LocalDate.now().year, frRequest.plantingMonth, 1)
        //fertilizers
        resp.ureaAvailable = frRequest.ureaAvailable
        resp.ureaCostPerBag = frRequest.ureaPrice
        resp.npkFifteenAvailable = frRequest.npk15Available
        resp.npkFifteenCostPerBag = frRequest.npk15Price
        resp.npkSeventeenAvailable = frRequest.npk17Available
        resp.npkSeventeenCostPerBag = frRequest.npk17Price


        //val resp = recService.computeFrRecommendation(basicFrRequest)
        return resp
    }

    fun fetchDefaultValues(country: String): PlumberComputeRequest {

        val endPoint = "${defaultUrl}/fr/${country.lowercase()}.json"
        val resp = restTemplate.getForEntity(endPoint, PlumberComputeRequest::class.java)
        return resp.body!!
    }
}