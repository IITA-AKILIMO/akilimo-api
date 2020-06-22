package com.acai.akilimo.request

import lombok.Data

@Data
class UserInfo(
        var mobileCountryCode: String,
        var mobileNumber: String,
        var fullPhoneNumber: String,
        var userName: String,
        var fieldDescription: String
) {

    @Deprecated("Will be moved to mandatory section in next release")
    var deviceToken: String? = null
    @Deprecated("Will be removed in the next release")
    var deviceID: String? = "NA"

    var emailAddress: String? = null

    var sendSms: Boolean = false
    var sendEmail: Boolean = false
}