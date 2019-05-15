package com.acai.akilimo.generators

import com.github.rholder.fauxflake.IdGenerators
import com.github.rholder.fauxflake.api.Id
import com.github.rholder.fauxflake.api.IdGenerator
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
        val id = snowflake.generateId(1000)

        return id.asLong()
    }
}
