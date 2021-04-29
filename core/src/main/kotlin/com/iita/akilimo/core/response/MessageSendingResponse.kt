package com.iita.akilimo.core.response

import org.springframework.http.HttpStatus

class MessageSendingResponse {


    var responseCode: Int = HttpStatus.BAD_REQUEST.value()

    var responseMessage: String = HttpStatus.BAD_REQUEST.reasonPhrase

    var httpStatus: HttpStatus = HttpStatus.BAD_REQUEST

    constructor()

    constructor(reasonCode: Int, reasonPhrase: String) {
        this.responseCode = reasonCode
        this.responseMessage = reasonPhrase
    }
}
