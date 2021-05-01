package com.iita.akilimo.database.entities

import com.iita.akilimo.database.entities.BaseEntity
import java.math.BigDecimal
import com.iita.akilimo.enums.EnumUserType
import org.hibernate.annotations.Immutable
import java.time.LocalDateTime
import javax.persistence.*

@Immutable //mark like this for view
@Entity
@Table(name = "v_app_request_stats_view")
class RequestStatsViewEntity : BaseEntity() {
    @Column(name = "request_date")
    var requestDate: LocalDateTime? = null

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

    @Column(name = "gender_name")
    var genderName: String? = null

    @Column(name = "gender", nullable = false)
    var gender: String? = null

    @Column(name = "phone_number")
    var phoneNumber: String? = null

    @Enumerated(EnumType.STRING)
    @Column(name = "user_type", nullable = false)
    var userType: EnumUserType? = null

    @Column(name = "use_case", nullable = false)
    var useCase: String? = null
}
