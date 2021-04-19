package com.acai.akilimo.controllers

import com.acai.akilimo.entities.UserFeedback
import com.acai.akilimo.request.SurveyRequest
import com.acai.akilimo.service.FeedbackService
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping("/api/v1/user-feedback")
@RestController
class UserFeedbackController(private val feedbackService: FeedbackService) {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(UserFeedbackController::class.java)
    }


    @PostMapping("/survey")
    fun addUserSurvey(
        @Valid @RequestBody surveyRequest: SurveyRequest
    ): ResponseEntity<UserFeedback> {

        val userFeedback = feedbackService.addUserFeedBack(surveyRequest)

        return ResponseEntity(userFeedback, HttpStatus.OK)
    }
}