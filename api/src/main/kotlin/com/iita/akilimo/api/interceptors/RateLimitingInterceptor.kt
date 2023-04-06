package com.iita.akilimo.api.interceptors

import com.fasterxml.jackson.databind.ObjectMapper
import com.iita.akilimo.core.response.MessageSendingResponse
import com.iita.akilimo.core.utils.SimpleRateLimiter
import com.iita.akilimo.enums.EnumRateTypes
import org.slf4j.LoggerFactory
import org.springframework.beans.factory.annotation.Value
import org.springframework.http.HttpStatus
import org.springframework.http.MediaType
import org.springframework.stereotype.Component
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter
import java.util.*
import java.util.concurrent.ConcurrentHashMap
import java.util.concurrent.Executors
import javax.annotation.PreDestroy
import javax.servlet.http.HttpServletRequest
import javax.servlet.http.HttpServletResponse


@Component
class RateLimitingInterceptor : HandlerInterceptorAdapter() {
    @Value("\${rate.limit.enabled}")
    private val enabled = false

    @Value("\${rate.limit.max-request}")
    private val maxRequests = 0

    @Value("\${rate.limit.rate-type}")
    private val rateType: EnumRateTypes = EnumRateTypes.SECOND

    private val scheduler = Executors.newScheduledThreadPool(10)
    var rateLimiter: SimpleRateLimiter? = null
    private val limiters: MutableMap<String, Optional<SimpleRateLimiter>> = ConcurrentHashMap()


    var mapper = ObjectMapper()

    companion object {
        private val logger = LoggerFactory.getLogger(this::class.java)
    }

    @Throws(Exception::class)
    override fun preHandle(request: HttpServletRequest, response: HttpServletResponse, handler: Any): Boolean {
        if (!enabled) {
            return true
        }

        val clientId = request.remoteAddr

        // let non-API requests pass
        rateLimiter = getRateLimiter(clientId).get()
        val allowRequest = rateLimiter!!.tryAcquire()
        if (!allowRequest) {
            response.status = HttpStatus.TOO_MANY_REQUESTS.value()

            val resp: MessageSendingResponse = MessageSendingResponse()
            resp.httpStatus = HttpStatus.TOO_MANY_REQUESTS
            resp.responseCode = HttpStatus.TOO_MANY_REQUESTS.value()
            resp.responseMessage = HttpStatus.TOO_MANY_REQUESTS.reasonPhrase

            response.contentType = MediaType.APPLICATION_JSON_VALUE
            response.writer.write(mapper.writeValueAsString(resp))
        }




        logger.info(
                "Request coming from origin: {} maximum request is {}",
                clientId,
                maxRequests.toString()
        )
        response.addHeader("X-RateLimit-Limit", maxRequests.toString())
        response.addHeader("X-RateLimit-Remaining", maxRequests.toString())
        response.addHeader("X-RateLimit-Reset", maxRequests.toString())

        return allowRequest
    }

    private fun getRateLimiter(clientId: String): Optional<SimpleRateLimiter> {
        return limiters.computeIfAbsent(clientId) { _clientId: String -> Optional.of(createRateLimiter(_clientId)) }
    }

    private fun createRateLimiter(applicationId: String): SimpleRateLimiter {
        logger.info("Creating rate limiter for applicationId={}", applicationId)
        return SimpleRateLimiter.create(maxRequests, rateType)
    }

    @PreDestroy
    fun destroy() {
        // loop and finalize all limiters
        rateLimiter!!.stop()
        scheduler.shutdown()
    }
}
