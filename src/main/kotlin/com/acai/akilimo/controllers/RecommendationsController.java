package com.acai.akilimo.controllers;


import com.acai.akilimo.config.ConfigProperties;
import com.acai.akilimo.entities.YieldRequest;
import com.acai.akilimo.entities.YieldResponse;
import com.acai.akilimo.properties.Plumber;
import com.acai.akilimo.requests.YieldRequestBody;
import com.acai.akilimo.service.YieldRequestServiceImp;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpEntity;
import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.client.RestTemplate;

import java.util.List;

@RequestMapping("/api/v2/recommendations")
@RestController
public class RecommendationsController {

    private Logger logger = LoggerFactory.getLogger(RecommendationsController.class);
    private final YieldRequestServiceImp yieldRequestServiceImp;


    private final Plumber plumberProperties;

    private final RestTemplate restTemplate;

    @Autowired
    public RecommendationsController(YieldRequestServiceImp yieldRequestServiceImp, RestTemplate restTemplate, ConfigProperties configProperties) {
        this.yieldRequestServiceImp = yieldRequestServiceImp;
        this.restTemplate = restTemplate;
        this.plumberProperties = configProperties.plumber();
    }

    @GetMapping
    public List<YieldRequest> listYieldRequests() {
        return yieldRequestServiceImp.findAll();
    }

    @PostMapping("/compute")
    public YieldRequest processYieldRequest(@RequestBody YieldRequest yieldRequest) {
        if (yieldRequest != null) {
            yieldRequestServiceImp.addYieldRequest(yieldRequest);
        }
        return yieldRequest;
    }

    @PostMapping("/compute/direct")
    public YieldResponse processDirectYieldRequest(@RequestBody YieldRequestBody yieldRequestBody) {
        YieldResponse yieldResponse = new YieldResponse();
        HttpHeaders headers = new HttpHeaders();

        headers.add("Content-Type", String.valueOf(MediaType.APPLICATION_JSON));

        logger.info("Payload is " + yieldRequestBody.toString());
        logger.info("Request has entered here, proceeding " + yieldRequestBody.getHarvestDate());
        if (yieldRequestBody != null) {
            //send to plumber
            HttpEntity<YieldRequestBody> entity = new HttpEntity<>(yieldRequestBody, headers);
            String theUrl = plumberProperties.getEndpoint() + "/estimate/compute";

            logger.info("Going to endpoint " + theUrl);
            ResponseEntity<YieldResponse[]> response = restTemplate.postForEntity(
                    theUrl, entity, YieldResponse[].class);

            Object[] objects = response.getBody();

            if (objects != null) {
                yieldResponse.setReccomendationText(objects[2].toString());
            }

            logger.info("Returning response to requesting client");
        }
        return yieldResponse;
    }
}
