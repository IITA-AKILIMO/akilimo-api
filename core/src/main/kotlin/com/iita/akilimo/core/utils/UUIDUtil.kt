package com.iita.akilimo.core.utils

import org.apache.commons.codec.binary.Hex
import java.nio.ByteBuffer
import java.util.*

/**
 * Utility class for working with UUIDs.
 */
object UUIDUtil {
    /**
     * Creates a [UUID] from its hex representation.
     *
     * @param uuid hex representation of uuid
     * @return [UUID] converted from the hex string
     * @throws org.apache.commons.codec.DecoderException thrown if the hex string cannot be decoded
     * @throws IndexOutOfBoundsException thrown if the string is not the proper length for a uuid
     */
    @Throws(Exception::class)
    fun fromHex(uuid: String): UUID {
        val data = Hex.decodeHex(uuid.toCharArray())
        return UUID(ByteBuffer.wrap(data, 0, 8).long, ByteBuffer.wrap(data, 8, 8).long)
    }

    /**
     * Gets the hex representation of the supplied [UUID]
     *
     * @param uuid uuid to convert to hex
     * @return hex representation of the uuid
     */
    fun toHex(uuid: UUID): String {
        val bytes = ByteBuffer.wrap(ByteArray(16))
        bytes.putLong(uuid.mostSignificantBits)
        bytes.putLong(uuid.leastSignificantBits)
        return String(Hex.encodeHex(bytes.array()))
    }
}
