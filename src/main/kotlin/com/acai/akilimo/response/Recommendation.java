
package com.acai.akilimo.response;

import java.util.List;
import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonPropertyOrder;
import org.apache.commons.lang3.builder.ToStringBuilder;

@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonPropertyOrder({
    "FertilizerRecommendation",
    "InterCropping",
    "PlantingPractices",
    "SP"
})
public class Recommendation {

    @JsonProperty("FertilizerRecommendation")
    public List<FertilizerRecommendation> fR = null;
    @JsonProperty("InterCropping")
    public List<InterCropping> iC = null;
    @JsonProperty("PlantingPractices")
    public List<PlantingPractices> pP = null;
    @JsonProperty("SP")
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

    @Override
    public String toString() {
        return new ToStringBuilder(this).append("fR", fR).append("iC", iC).append("pP", pP).append("sP", sP).toString();
    }

}
