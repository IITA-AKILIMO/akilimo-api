
package com.acai.akilimo.response;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonPropertyOrder;

@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonPropertyOrder({
    "dMP",
    "dNR",
    "dTC",
    "rec_F",
    "rec_D",
    "reason_D",
    "urea",
    "NPK15_15_15"
})
public class InterCropping {

    @JsonProperty("dMP")
    public Integer dMP;
    @JsonProperty("dNR")
    public Float dNR;
    @JsonProperty("dTC")
    public Float dTC;
    @JsonProperty("rec_F")
    public Boolean recF;
    @JsonProperty("rec_D")
    public Boolean recD;
    @JsonProperty("reason_D")
    public String reasonD;
    @JsonProperty("urea")
    public Float urea;
    @JsonProperty("NPK15_15_15")
    public Float nPK151515;

    /**
     * No args constructor for use in serialization
     *
     */
    public InterCropping() {
    }

    /**
     *
     * @param reasonD
     * @param dNR
     * @param nPK151515
     * @param urea
     * @param dTC
     * @param recD
     * @param recF
     * @param dMP
     */
    public InterCropping(Integer dMP, Float dNR, Float dTC, Boolean recF, Boolean recD, String reasonD, Float urea, Float nPK151515) {
        super();
        this.dMP = dMP;
        this.dNR = dNR;
        this.dTC = dTC;
        this.recF = recF;
        this.recD = recD;
        this.reasonD = reasonD;
        this.urea = urea;
        this.nPK151515 = nPK151515;
    }

}
