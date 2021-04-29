
package com.iita.akilimo.core.response;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonPropertyOrder;

import java.util.List;

@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonPropertyOrder({
    "FertilizerRecommendation",
    "InterCropping",
    "PlantingPractices",
    "ScheduledPlanting"
})
public class Recommendation {

    @JsonProperty("FertilizerRecommendation")
    public List<FertilizerRecommendation> fR = null;
    @JsonProperty("InterCropping")
    public List<InterCropping> iC = null;
    @JsonProperty("PlantingPractices")
    public List<PlantingPractices> pP = null;
    @JsonProperty("ScheduledPlanting")
    public List<String> sP = null;

    /**
     * No args constructor for use in serialization
     *
     */
    public Recommendation() {
    }

    /**
     *
     * @param sP
     * @param pP
     * @param iC
     * @param fR
     */
    public Recommendation(List<FertilizerRecommendation> fR, List<InterCropping> iC, List<PlantingPractices> pP, List<String> sP) {
        super();
        this.fR = fR;
        this.iC = iC;
        this.pP = pP;
        this.sP = sP;
    }
}
