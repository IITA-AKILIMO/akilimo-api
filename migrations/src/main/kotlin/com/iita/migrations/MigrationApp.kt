package com.iita.migrations

import org.springframework.boot.autoconfigure.SpringBootApplication
import org.springframework.boot.runApplication

@SpringBootApplication
class MigrationApp

fun main(args: Array<String>) {
    runApplication<MigrationApp>(*args)
}
