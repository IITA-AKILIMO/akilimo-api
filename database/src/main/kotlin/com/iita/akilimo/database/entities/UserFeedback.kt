package com.iita.akilimo.database.entities

import com.acai.akilimo.enums.EnumUserType
import javax.persistence.*

@Entity
@Table(name = "user_feedback")
class UserFeedback : BaseEntity() {


    @Column(name = "device_token", columnDefinition = "TEXT")
    var deviceToken: String? = null

    @Column(name = "akilimo_usage", columnDefinition = "TEXT")
    var akilimoUsage: String? = null

    @Enumerated(EnumType.STRING)
    @Column(name = "user_type", columnDefinition = "TEXT")
    var userType: EnumUserType? = null

    @Column(name = "akilimo_rec_rating")
    var akilimoRecRating: Int? = null

    @Column(name = "akilimo_useful_rating")
    var akilimoUsefulRating: Int? = null

    @Column(name = "language")
    var language: String? = null
}
