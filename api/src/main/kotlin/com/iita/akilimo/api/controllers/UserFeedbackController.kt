package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.mapper.UserFeedbackDto
import com.iita.akilimo.core.request.UserFeedBackRequest
import com.iita.akilimo.core.service.FeedbackService
import com.iita.akilimo.database.entities.UserFeedback
import io.swagger.v3.oas.annotations.Operation
import io.swagger.v3.oas.annotations.Parameter
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
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
    @Operation(summary = "Add user feedback", description = "", tags = ["User Feedback"])
    fun addUserFeedback(
        @Valid @RequestBody userFeedBackRequest: UserFeedBackRequest
    ): ResponseEntity<UserFeedbackDto> {

        val userFeedback = feedbackService.addUserFeedBack(userFeedBackRequest)

        return ResponseEntity(userFeedback, HttpStatus.CREATED)
    }

    @GetMapping
    @Operation(summary = "Add user feedback", description = "", tags = ["User Feedback"])
    fun listFeedback(@Parameter(hidden = true) pageable: Pageable): ResponseEntity<Page<UserFeedbackDto>> {

        val userFeedback: Page<UserFeedbackDto> = feedbackService.listFeedBack(pageable)

        return ResponseEntity(userFeedback, HttpStatus.OK)
    }
}
