package com.acai.akilimo.generators


import org.slf4j.LoggerFactory
import org.springframework.core.io.ClassPathResource
import java.io.FileInputStream
import java.nio.file.Files
import java.sql.Timestamp
import com.github.jhonnymertz.wkhtmltopdf.wrapper.Pdf
import com.github.jhonnymertz.wkhtmltopdf.wrapper.configurations.WrapperConfig
import com.github.jhonnymertz.wkhtmltopdf.wrapper.params.Param


class HtmlFileToPdfGenerator {
    private val logger = LoggerFactory.getLogger(HtmlFileToPdfGenerator::class.java)

    fun readHtmlFileTest() {
        try {
            val timestamp = Timestamp(System.currentTimeMillis())

            val time  = timestamp.time

            val url = ClassPathResource("computed/fertilizer_advice_pp.html").file
            val text = String(Files.readAllBytes(url.toPath()))
            //val url = ClassPathResource("computed/test.html").file.toURI().toString()
            logger.info("URL: $url")

            val wrapperConfig = WrapperConfig("C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe")

            wrapperConfig.wkhtmltopdfCommand = "C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe"

            val pdf = Pdf(wrapperConfig)

            pdf.addPageFromString(text)
            pdf.addParam(Param("-O", "Landscape"))
            pdf.addParam(Param("--page-size", "Legal"))
            pdf.addParam(Param("--enable-javascript"))
            pdf.saveAs("$time.pdf")
        } catch (e: Exception) {
            e.printStackTrace()
        }

    }


    fun readHtmlFile(): String {

        val timestamp = Timestamp(System.currentTimeMillis())

        val time  = timestamp.time

        val htmlSource = ClassPathResource("computed/fertilizer_advice_pp.html").file
        //val htmlSource = ClassPathResource("computed/test.html").file

        val targetStream = FileInputStream(htmlSource)

        /*

        val tidy = Tidy()
        tidy.xhtml = true
        tidy.xmlTags = true

        tidy.parse(targetStream, System.out)


        val document = Document(PageSize.A4.rotate(),0F,0F,0F,0F)
        val writer = PdfWriter.getInstance(document,FileOutputStream("$time.pdf"))
        document.open()

        XMLWorkerHelper.getInstance().parseXHtml(writer, document,targetStream)
        document.close()*/

        return "9000"
    }

    fun readHtmlFileOld(): String {
        val resource = ClassPathResource("computed/fertilizer_advice_pp.html").file
        val text = String(Files.readAllBytes(resource.toPath()))




        // step 1
        /*val document = Document()
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
       // val dest = "akilimo.pdf"
       // val writer = PdfWriter(dest)

        // Creating a PdfDocument
       // val pdfDoc = PdfDocument(writer)

        // Adding a new page
        //pdfDoc.addNewPage()

        // Creating a Document
        //val document = Document(pdfDoc)


        // Closing the document
       // document.close()
        logger.info("PDF Created")


        return "9"
    }
}