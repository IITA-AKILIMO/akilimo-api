package com.iita.akilimo.core.request

class UserInfo(
    val mobileCountryCode: String,
    val mobileNumber: String,
    val fullPhoneNumber: String,
    val userName: String,
    val fieldDescription: String,
    val deviceToken: String,
    val gender: String? = null
) {

    var emailAddress: String? = null
    var sendSms: Boolean = false
    var sendEmail: Boolean = false
}
