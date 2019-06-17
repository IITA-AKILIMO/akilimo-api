
package com.acai.akilimo.response.compute;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;

@JsonInclude(JsonInclude.Include.NON_NULL)
public class ComputeResponse {

    @JsonProperty("FR")
    public List<String> fertilizerRecommendation = null;
    @JsonProperty("IC")
    public List<String> interCropping = null;
    @JsonProperty("PP")
    public List<PlantingPractices> plantingPractices = null;
    @JsonProperty("SP")
    public List<ScheduledPlanting> scheduledPlanting = null;

}
