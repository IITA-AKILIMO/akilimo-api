
package com.acai.akilimo.response.compute;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonPropertyOrder;

@JsonInclude(JsonInclude.Include.NON_NULL)
public class ScheduledPlanting {

    @JsonProperty("plw")
    public int plw;
    @JsonProperty("haw")
    public int haw;
    @JsonProperty("rPWnr")
    public int rPWnr;
    @JsonProperty("rHWnr")
    public int rHWnr;
    @JsonProperty("PD")
    public String pD;
    @JsonProperty("HD")
    public String hD;
    @JsonProperty("CY")
    public double cY;
    @JsonProperty("WY")
    public double wY;
    @JsonProperty("RFCY")
    public double rFCY;
    @JsonProperty("RFWY")
    public double rFWY;
    @JsonProperty("RY")
    public double rY;
    @JsonProperty("RP")
    public double rP;
    @JsonProperty("rootUP")
    public int rootUP;
    @JsonProperty("GR")
    public double gR;
    @JsonProperty("CP")
    public boolean cP;
    @JsonProperty("dGR")
    public double dGR;

}
