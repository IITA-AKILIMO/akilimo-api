package com.iita.akilimo.core.request

class CSVDownloadRequest(
    val fileName: String,
) {
    var subFolder: String? = null
}
