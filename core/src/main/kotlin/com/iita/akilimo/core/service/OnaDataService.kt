package com.iita.akilimo.core.service


import com.iita.akilimo.config.AkilimoConfigProperties
import com.iita.akilimo.core.request.CSVDownloadRequest
import com.iita.akilimo.core.response.CSVDownloadResponse
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.core.io.ByteArrayResource
import org.springframework.stereotype.Service
import java.io.File
import java.nio.file.Files
import java.nio.file.Path
import java.nio.file.Paths


@Service
class OnaDataService
constructor(
    akilimoConfig: AkilimoConfigProperties
) {

    private val logger = LoggerFactory.getLogger(this::class.java)

    private val modelMapper = ModelMapper()

    private val onaData = akilimoConfig.onaData()


    fun processCsv(downloadRequest: CSVDownloadRequest): CSVDownloadResponse {

        val folderPath = downloadRequest.subFolder
        var fullPath = "${onaData.csvPath}${downloadRequest.fileName}"
        if (folderPath != null) {
            if (folderPath.isNotEmpty()) {
                val fileSeparator = File.separator;
                fullPath = "${onaData.csvPath}${fileSeparator}${folderPath}${fileSeparator}${downloadRequest.fileName}"
            }
        }

        logger.info("Downloading file $fullPath")
        val file = File(fullPath)

        val path: Path = Paths.get(file.absolutePath)
        val resource = ByteArrayResource(Files.readAllBytes(path))

        return CSVDownloadResponse(file, resource)
    }

}
