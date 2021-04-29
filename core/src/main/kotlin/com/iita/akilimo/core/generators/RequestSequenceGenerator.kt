package com.iita.akilimo.core.generators

import com.github.rholder.fauxflake.IdGenerators
import org.hibernate.HibernateException
import org.hibernate.engine.spi.SharedSessionContractImplementor
import org.hibernate.id.IdentifierGenerator
import java.io.Serializable


class RequestSequenceGenerator : IdentifierGenerator {

    @Throws(HibernateException::class)
    override fun generate(session: SharedSessionContractImplementor, `object`: Any): Serializable {
        var generatedSequence: Long = 0
        try {
            generatedSequence = generateDatabaseSequence()
        } catch (e: InterruptedException) {
            e.printStackTrace()
        }


        return generatedSequence
    }

    @Throws(InterruptedException::class)
    private fun generateDatabaseSequence(): Long {

        val snowflake = IdGenerators.newSnowflakeIdGenerator()
        val id = snowflake.generateId(3001)

        return id.asLong()
    }
}
