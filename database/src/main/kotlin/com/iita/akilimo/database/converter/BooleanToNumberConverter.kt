package com.iita.akilimo.database.converter

import javax.persistence.AttributeConverter
import javax.persistence.Converter

@Converter
class BooleanToNumberConverter : AttributeConverter<Boolean, Int> {

    override fun convertToDatabaseColumn(value: Boolean): Int {
        return if (value) {
            1
        } else 0
    }

    override fun convertToEntityAttribute(value: Int): Boolean {
        return value == 1
    }
}
