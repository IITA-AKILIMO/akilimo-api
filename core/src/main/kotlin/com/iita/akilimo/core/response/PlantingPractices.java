
package com.iita.akilimo.core.response;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonPropertyOrder;

@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonPropertyOrder({
    "ploughing",
    "ridging",
    "method_ploughing",
    "method_ridging",
    "RP",
    "TC",
    "GR",
    "NR",
    "CP",
    "dTC",
    "dRP",
    "dGR",
    "dNR"
})
public class PlantingPractices {

    @JsonProperty("ploughing")
    public Boolean ploughing;
    @JsonProperty("ridging")
    public Boolean ridging;
    @JsonProperty("method_ploughing")
    public String methodPloughing;
    @JsonProperty("method_ridging")
    public String methodRidging;
    @JsonProperty("RP")
    public Integer rP;
    @JsonProperty("TC")
    public Float tC;
    @JsonProperty("GR")
    public Integer gR;
    @JsonProperty("NR")
    public Float nR;
    @JsonProperty("CP")
    public Boolean cP;
    @JsonProperty("dTC")
    public Float dTC;
    @JsonProperty("dRP")
    public Integer dRP;
    @JsonProperty("dGR")
    public Integer dGR;
    @JsonProperty("dNR")
    public Float dNR;

    /**
     * No args constructor for use in serialization
     *
     */
    public PlantingPractices() {
    }

    /**
     *
     * @param dNR
     * @param ploughing
     * @param dRP
     * @param rP
     * @param nR
     * @param cP
     * @param dGR
     * @param dTC
     * @param methodRidging
     * @param ridging
     * @param tC
     * @param methodPloughing
     * @param gR
     */
    public PlantingPractices(Boolean ploughing, Boolean ridging, String methodPloughing, String methodRidging, Integer rP, Float tC, Integer gR, Float nR, Boolean cP, Float dTC, Integer dRP, Integer dGR, Float dNR) {
        super();
        this.ploughing = ploughing;
        this.ridging = ridging;
        this.methodPloughing = methodPloughing;
        this.methodRidging = methodRidging;
        this.rP = rP;
        this.tC = tC;
        this.gR = gR;
        this.nR = nR;
        this.cP = cP;
        this.dTC = dTC;
        this.dRP = dRP;
        this.dGR = dGR;
        this.dNR = dNR;
    }

}
