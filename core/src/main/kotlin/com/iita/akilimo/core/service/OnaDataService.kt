package com.iita.akilimo.core.service


import com.iita.akilimo.config.AkilimoConfigProperties
import com.iita.akilimo.core.request.CSVDownloadRequest
import com.iita.akilimo.core.response.CSVDownloadResponse
import org.apache.commons.csv.CSVFormat
import org.apache.commons.csv.CSVRecord
import org.modelmapper.ModelMapper
import org.slf4j.LoggerFactory
import org.springframework.core.io.ByteArrayResource
import org.springframework.security.config.Elements.HEADERS
import org.springframework.stereotype.Service
import java.io.File
import java.io.FileReader
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

//        val regex = "\\.[0-9a-z]+$".toRegex()
        val regex = "\\.(csv|xls|xlsx)$".toRegex()
        val ext = regex.find(downloadRequest.fileName)

        var fileName = downloadRequest.fileName
        if (ext == null) {
            fileName = "${downloadRequest.fileName}.csv"
        }

        var fullPath = "${onaData.csvPath}${fileName}"
        if (folderPath != null) {
            if (folderPath.isNotEmpty()) {
                val fileSeparator = File.separator;
                fullPath = "${onaData.csvPath}${fileSeparator}${folderPath}${fileSeparator}${fileName}"
            }
        }

        logger.info("Downloading file $fullPath")
        val file = File(fullPath)

        val reader = FileReader(fullPath)
        val records: Iterable<CSVRecord> = CSVFormat.DEFAULT
//            .withHeader(HEADERS)
            .withFirstRecordAsHeader()
            .parse(reader)

        var recordNumber: Int = 0
        records.forEach { _ ->
            recordNumber = recordNumber + 1
        }

        val path: Path = Paths.get(file.absolutePath)
        val resource = ByteArrayResource(Files.readAllBytes(path))

        return CSVDownloadResponse(file, resource, recordNumber)
    }

}
