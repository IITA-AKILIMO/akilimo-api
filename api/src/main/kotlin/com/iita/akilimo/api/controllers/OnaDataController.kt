package com.iita.akilimo.api.controllers

import com.iita.akilimo.core.request.CSVDownloadRequest
import com.iita.akilimo.core.response.CSVDownloadResponse
import com.iita.akilimo.core.service.OnaDataService
import io.swagger.v3.oas.annotations.Operation
import org.slf4j.Logger
import org.slf4j.LoggerFactory
import org.springframework.core.io.ByteArrayResource
import org.springframework.http.HttpHeaders
import org.springframework.http.HttpStatus
import org.springframework.http.MediaType
import org.springframework.http.ResponseEntity
import org.springframework.web.bind.annotation.*
import javax.validation.Valid


@RequestMapping("/api/v1/ona-data")
@RestController
class OnaDataController(private val onaDataService: OnaDataService) {

    companion object {
        val logger: Logger = LoggerFactory.getLogger(this::class.java)
    }

    //    @RequestMapping(value = ["/download"], method = [RequestMethod.GET, RequestMethod.POST])
    @PostMapping("/download")
    @Operation(summary = "Download csv files", description = "", tags = ["Ona data"])
    fun download(@Valid @RequestBody cSvDownloadRequest: CSVDownloadRequest): ResponseEntity<ByteArrayResource> {
        val csvDownload = onaDataService.processCsv(cSvDownloadRequest)

        val respHeaders = HttpHeaders()
        respHeaders.contentLength = csvDownload.file.length()
        respHeaders.contentType = MediaType.APPLICATION_OCTET_STREAM//MediaType("text", "csv")
        respHeaders.cacheControl = "must-revalidate, post-check=0, pre-check=0"
        respHeaders.set(HttpHeaders.CONTENT_DISPOSITION, "attachment; filename=${cSvDownloadRequest.fileName}")
        respHeaders.set("File-Name", cSvDownloadRequest.fileName)
        respHeaders.set("Record-Count", "${csvDownload.recordNumber}")

        return ResponseEntity(csvDownload.resource, respHeaders, HttpStatus.OK)
    }
}
