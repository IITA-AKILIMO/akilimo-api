package com.iita.akilimo.core.service.basicrec

import com.iita.akilimo.core.mapper.RecommendationResponseDto
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

    fun computeFrRecommendation(frRequest: BasicFrRequest): RecommendationResponseDto {
        //assign default values now
        val plumberComputeRequest = fetchDefaultValues(frRequest.country)
        plumberComputeRequest.fertilizerRec = true
        plumberComputeRequest.country = frRequest.country
        plumberComputeRequest.currentFieldYield = frRequest.currentFieldYield
        plumberComputeRequest.fieldArea = frRequest.area
        plumberComputeRequest.areaUnits = frRequest.areaUnit
        plumberComputeRequest.mapLat = frRequest.lat
        plumberComputeRequest.mapLong = frRequest.lon

        //compute the planting dates
        val plantingDate = LocalDate.of(LocalDate.now().year, frRequest.plantingMonth, 1)
        val harvestDate = plantingDate.plusMonths(12);//Increment by 12 months
        plumberComputeRequest.plantingDate = plantingDate.toString()
        plumberComputeRequest.harvestDate = harvestDate.toString()
        //fertilizers
        plumberComputeRequest.ureaAvailable = frRequest.ureaAvailable
        plumberComputeRequest.ureaCostPerBag = frRequest.ureaPrice
        plumberComputeRequest.npkFifteenAvailable = frRequest.npk15Available
        plumberComputeRequest.npkFifteenCostPerBag = frRequest.npk15Price
        plumberComputeRequest.npkSeventeenAvailable = frRequest.npk17Available
        plumberComputeRequest.npkSeventeenCostPerBag = frRequest.npk17Price


        return recService.processPlumberRequest(plumberComputeRequest, recService.setHTTPHeaders())
    }

    fun fetchDefaultValues(country: String): PlumberComputeRequest {

        val endPoint = "${defaultUrl}/fr/${country.lowercase()}.json"
        val resp = restTemplate.getForEntity(endPoint, PlumberComputeRequest::class.java)
        return resp.body!!
    }
}
