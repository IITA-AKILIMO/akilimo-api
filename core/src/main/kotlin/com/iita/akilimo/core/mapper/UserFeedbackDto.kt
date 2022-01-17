package com.iita.akilimo.core.mapper

import com.iita.akilimo.database.entities.BaseEntity
import com.iita.akilimo.enums.EnumUserType
import javax.persistence.*


class UserFeedbackDto : BaseDto() {

    var id: Long? = null

    var deviceToken: String? = null

    var akilimoUsage: String? = null

    var userType: EnumUserType? = null

    var akilimoRecRating: Int? = null

    var akilimoUsefulRating: Int? = null


    var language: String? = null
}
