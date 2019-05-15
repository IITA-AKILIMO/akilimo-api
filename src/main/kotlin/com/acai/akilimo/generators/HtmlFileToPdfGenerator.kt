package com.acai.akilimo.generators

import com.google.common.io.CharStreams
import org.springframework.core.io.ResourceLoader
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.beans.factory.annotation.Value
import org.springframework.core.io.Resource
import org.springframework.stereotype.Service
import java.io.IOException
import java.io.InputStreamReader
import java.nio.file.Files.readAllBytes
import org.springframework.core.io.ClassPathResource
import java.nio.file.Files
import java.io.FileInputStream
import java.io.FileOutputStream
import com.itextpdf.kernel.pdf.PdfWriter
import com.itextpdf.layout.Document
import com.itextpdf.kernel.pdf.PdfDocument




class HtmlFileToPdfGenerator {
    @Throws(IOException::class)
    fun readHtmlFile(): String {
        val resource = ClassPathResource("computed/fertilizer_advice_pp.html").file
        val text = String(Files.readAllBytes(resource.toPath()))


        /*
        // step 1
        val document = Document()
        // step 2
        val writer = PdfWriter.getInstance(document, FileOutputStream("pdf.pdf"))
        // step 3
        document.open()
        // step 4
        XMLWorkerHelper.getInstance().parseXHtml(writer, document,
                FileInputStream("index.html"))
        //step 5
        document.close()

        println("PDF Created!")
         */

        // Creating a PdfWriter
        val dest = "akilimo.pdf"
        val writer = PdfWriter(dest)

        // Creating a PdfDocument
        val pdfDoc = PdfDocument(writer)

        // Adding a new page
        pdfDoc.addNewPage()

        // Creating a Document
        val document = Document(pdfDoc)

        // Closing the document
        document.close()
        println("PDF Created")


        return "9"
    }
}