package com.acai.akilimo.service;

import com.acai.akilimo.entities.Fertilizer;
import com.acai.akilimo.entities.YieldRequest;
import com.acai.akilimo.repositories.YieldRequestRepository;
import com.acai.akilimo.service.interfaces.IYieldRequestService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.List;
import java.util.Set;

@Service
public class YieldRequestServiceImp implements IYieldRequestService {

    private final YieldRequestRepository yieldRequestRepository;

    @Autowired
    public YieldRequestServiceImp(YieldRequestRepository yieldRequestRepository) {
        this.yieldRequestRepository = yieldRequestRepository;
    }

    @Override
    public YieldRequest addYieldRequest(YieldRequest yieldRequest) {
        try {
            Set<Fertilizer> fertilizerList = yieldRequest.addFertilizers(yieldRequest);

            yieldRequest.setFertilizers(fertilizerList);

            return yieldRequestRepository.save(yieldRequest);
        } catch (Exception ex) {
            ex.printStackTrace();
        }
        return null;
    }

    @Override
    public List<YieldRequest> findAll() {
        return yieldRequestRepository.findAll();
    }
}
