
package com.acai.akilimo.response.compute;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonPropertyOrder;

@JsonInclude(JsonInclude.Include.NON_NULL)
public class PlantingPractices {

    @JsonProperty("ploughing")
    public boolean ploughing;
    @JsonProperty("ridging")
    public boolean ridging;
    @JsonProperty("method_ploughing")
    public String methodPloughing;
    @JsonProperty("method_ridging")
    public String methodRidging;
    @JsonProperty("RP")
    public int rP;
    @JsonProperty("TC")
    public double tC;
    @JsonProperty("GR")
    public int gR;
    @JsonProperty("NR")
    public double nR;
    @JsonProperty("CP")
    public boolean cP;
    @JsonProperty("dTC")
    public double dTC;
    @JsonProperty("dRP")
    public int dRP;
    @JsonProperty("dGR")
    public int dGR;
    @JsonProperty("dNR")
    public double dNR;

}
