import java.time.LocalDateTime
import java.time.format.DateTimeFormatter

plugins {
    id("org.springframework.boot")
    kotlin("jvm")
    kotlin("plugin.spring")
}

val generateChangelog by tasks.registering {
    val changeName: String? by project
    if (changeName.isNullOrEmpty()) {
        return@registering
    }
    val date = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyyMMddHHmmss"))
    val changeSetName = changeName?.replace(" ", "_")?.toLowerCase()
    val user = project.findProperty("author") ?: System.getProperty("user.name")
    val file = File("$projectDir/src/main/resources/liquibase/changelog/${date}_$changeSetName.xml")
    val text = """<?xml version="1.0" encoding="utf-8"?>
<databaseChangeLog
        xmlns="http://www.liquibase.org/xml/ns/dbchangelog"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.liquibase.org/xml/ns/dbchangelog http://www.liquibase.org/xml/ns/dbchangelog/dbchangelog-3.4.xsd">

    <property name="autoIncrement" value="true" dbms="mysql,mariadb,h2"/>
    <property name="autoIncrement" value="false" dbms="oracle"/>

    <changeSet id="$date" author="$user" labels="akilimo-migration">

    </changeSet>
</databaseChangeLog>
"""
    file.writeText(text)
}

dependencies {
    implementation(kotlin("stdlib"))
    implementation(kotlin("stdlib-jdk8"))

    implementation("org.springframework.boot:spring-boot-starter")
    implementation("org.springframework.boot:spring-boot-starter-jdbc")

    implementation("org.liquibase:liquibase-core:4.8.0")
    runtimeOnly("org.mariadb.jdbc:mariadb-java-client:3.0.6")
}
