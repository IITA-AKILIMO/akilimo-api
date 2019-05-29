
package com.acai.akilimo.response;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonPropertyOrder;
import org.apache.commons.lang3.builder.ToStringBuilder;

@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonPropertyOrder({
    "N",
    "P",
    "K",
    "WLY",
    "CurrentY",
    "TargetY",
    "TC",
    "NR",
    "urea",
    "NPK15_15_15"
})
public class FertilizerRecommendation {

    @JsonProperty("N")
    public Float n;
    @JsonProperty("P")
    public Float p;
    @JsonProperty("K")
    public Float k;
    @JsonProperty("WLY")
    public Float wLY;
    @JsonProperty("CurrentY")
    public Float currentY;
    @JsonProperty("TargetY")
    public Float targetY;
    @JsonProperty("TC")
    public Float tC;
    @JsonProperty("NR")
    public Integer nR;
    @JsonProperty("urea")
    public Float urea;
    @JsonProperty("NPK15_15_15")
    public Float nPK151515;

    /**
     * No args constructor for use in serialization
     * 
     */
    public FertilizerRecommendation() {
    }

    /**
     * 
     * @param nPK151515
     * @param urea
     * @param nR
     * @param tC
     * @param p
     * @param n
     * @param targetY
     * @param currentY
     * @param wLY
     * @param k
     */
    public FertilizerRecommendation(Float n, Float p, Float k, Float wLY, Float currentY, Float targetY, Float tC, Integer nR, Float urea, Float nPK151515) {
        super();
        this.n = n;
        this.p = p;
        this.k = k;
        this.wLY = wLY;
        this.currentY = currentY;
        this.targetY = targetY;
        this.tC = tC;
        this.nR = nR;
        this.urea = urea;
        this.nPK151515 = nPK151515;
    }

    @Override
    public String toString() {
        return new ToStringBuilder(this).append("n", n).append("p", p).append("k", k).append("wLY", wLY).append("currentY", currentY).append("targetY", targetY).append("tC", tC).append("nR", nR).append("urea", urea).append("nPK151515", nPK151515).toString();
    }

}
