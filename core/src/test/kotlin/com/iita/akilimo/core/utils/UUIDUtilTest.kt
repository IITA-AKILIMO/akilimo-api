package com.iita.akilimo.core.utils

import org.junit.jupiter.api.Assertions.assertEquals
import org.junit.jupiter.api.Assertions.assertNotNull
import org.junit.jupiter.api.Test
import java.util.*

internal class UUIDUtilTest {

    @Test
    fun fromHex() {
        val uuid = UUIDUtil.fromHex("b930a505249b415c92f5ba7eeac32cf5")
        println(uuid.toString())
        assertEquals("b930a505-249b-415c-92f5-ba7eeac32cf5", uuid.toString())
    }

    @Test
    fun toHex() {
        val uuid = UUID.fromString("b930a505-249b-415c-92f5-ba7eeac32cf5")
        val hexVal = UUIDUtil.toHex(uuid)
        println(hexVal)
        assertEquals("b930a505249b415c92f5ba7eeac32cf5", hexVal)
    }

    @Test
    fun generateUUID() {
        val uuid = UUIDUtil.generateUUID()
        println(uuid.toString())
        assertNotNull(uuid)
    }
}
