package com.acai.akilimo.service.interfaces;


import com.acai.akilimo.entities.YieldRequest;

import java.util.List;

public interface IYieldRequestService {

    YieldRequest addYieldRequest(YieldRequest yieldRequest);

    List<YieldRequest> findAll();
}
