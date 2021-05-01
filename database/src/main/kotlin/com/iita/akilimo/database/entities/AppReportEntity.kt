package com.iita.akilimo.database.entities

import java.math.BigDecimal
import javax.persistence.Column
import javax.persistence.Entity
import javax.persistence.Table

@Entity
@Table(name = "app_report")
class AppReportEntity : BaseEntity() {
    @Column(name = "device_token")
    var deviceToken: String? = null

    @Column(name = "country_code")
    var countryCode: String? = null

    @Column(name = "lat")
    var lat: BigDecimal? = null

    @Column(name = "lon")
    var lon: BigDecimal? = null

    @Column(name = "full_names")
    var fullNames: String? = null

    @Column(name = "phone_number")
    var phoneNumber: String? = null

    @Column(name = "gender")
    var gender: String? = null

    @Column(name = "user_type")
    var userType: String? = null

    @Column(name = "fr")
    var fr: Boolean? = null

    @Column(name = "ic")
    var ic: Boolean? = null

    @Column(name = "pp")
    var pp: Boolean? = null

    @Column(name = "spp")
    var spp: Boolean? = null

    @Column(name = "sph")
    var sph: Boolean? = null
}
