package com.acai.akilimo.entities

import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "user_feedback")
class UserFeedback : BaseEntity() {


    @Column(name = "device_token", columnDefinition = "TEXT")
    var deviceToken: String? = null

    @Column(name = "akilimo_usage", columnDefinition = "TEXT")
    var akilimoUsage: String? = null

    @Column(name = "akilimo_rec_rating")
    var akilimoRecRating: Int? = null

    @Column(name = "akilimo_useful_rating")
    var akilimoUsefulRating: Int? = null

    @Column(name = "language")
    var language: String? = null
}