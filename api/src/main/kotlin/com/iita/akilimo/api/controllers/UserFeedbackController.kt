package com.acai.akilimo.controllers

import com.iita.akilimo.core.request.UserFeedBackRequest
import com.iita.akilimo.core.service.FeedbackService
import com.iita.akilimo.database.entities.UserFeedback
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.PostMapping
import org.springframework.web.bind.annotation.RequestBody
import org.springframework.web.bind.annotation.RequestMapping
import org.springframework.web.bind.annotation.RestController
import javax.validation.Valid


@RequestMapping("/api/v1/user-feedback")
@RestController
class UserFeedbackController(private val feedbackService: FeedbackService) {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(UserFeedbackController::class.java)
    }


    @PostMapping("/survey")
    fun addUserSurvey(
        @Valid @RequestBody userFeedBackRequest: UserFeedBackRequest
    ): ResponseEntity<UserFeedback> {

        val userFeedback = feedbackService.addUserFeedBack(userFeedBackRequest)

        return ResponseEntity(userFeedback, HttpStatus.OK)
    }
}
