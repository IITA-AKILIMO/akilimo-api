package com.acai.akilimo.requests;

import lombok.Data;

@Data
public class YieldRequestBody {
    private String areaUnits;
    private double area;
    private String plantingDate;
    private String harvestDate;
}
