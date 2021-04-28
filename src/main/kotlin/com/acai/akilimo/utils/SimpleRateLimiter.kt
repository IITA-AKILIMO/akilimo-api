package com.acai.akilimo.utils

import com.acai.akilimo.enums.EnumRateTypes
import java.util.concurrent.Executors
import java.util.concurrent.ScheduledExecutorService
import java.util.concurrent.Semaphore
import java.util.concurrent.TimeUnit

class SimpleRateLimiter private constructor(permits: Int, timePeriod: TimeUnit) {
    private val semaphore: Semaphore
    private val maxPermits: Int
    private val timePeriod: TimeUnit
    private var scheduler: ScheduledExecutorService? = null
    fun tryAcquire(): Boolean {
        return semaphore.tryAcquire()
    }

    fun stop() {
        scheduler!!.shutdownNow()
    }

    fun schedulePermitReplenishment() {
        scheduler = Executors.newScheduledThreadPool(1)
        scheduler?.scheduleAtFixedRate(Runnable { semaphore.release(maxPermits - semaphore.availablePermits()) }, 1, 1, timePeriod)
    }

    companion object {
        @JvmStatic
        fun create(permits: Int, enumRateTypes: EnumRateTypes): SimpleRateLimiter {
            val timeUnit = when (enumRateTypes) {
                EnumRateTypes.SECOND -> {
                    TimeUnit.SECONDS
                }
                EnumRateTypes.MINUTE -> {
                    TimeUnit.MINUTES
                }
                EnumRateTypes.HOUR -> {
                    TimeUnit.HOURS
                }
            }
            val limiter = SimpleRateLimiter(permits, timeUnit)
            limiter.schedulePermitReplenishment()
            return limiter
        }
    }

    init {
        semaphore = Semaphore(permits)
        maxPermits = permits
        this.timePeriod = timePeriod
    }
}
