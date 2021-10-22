package com.iita.akilimo.core.response

import org.springframework.core.io.ByteArrayResource
import java.io.File

class CSVDownloadResponse(val file: File, val resource: ByteArrayResource, val recordNumber: Int)
