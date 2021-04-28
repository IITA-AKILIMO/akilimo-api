package com.acai.akilimo.mapper

import com.fasterxml.jackson.annotation.JsonProperty
import com.fasterxml.jackson.annotation.JsonPropertyOrder

import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.Column


class StarchFactoryDto {


    var factoryName: String? = null


    var factoryLabel: String? = null

    var countryCode: String? = null

    var factoryNameCountry: String? = null

    var factoryActive: Boolean = false

    var createdAt: LocalDateTime? = null

    var updatedAt: LocalDateTime? = null

}
