import org.jetbrains.kotlin.gradle.tasks.KotlinCompile
import java.time.LocalDateTime
import java.time.format.DateTimeFormatter
import java.util.*


plugins {
    val kotlinVersion = "1.3.71"
    val springVersion = "2.2.6.RELEASE"
    val javaVersion = JavaVersion.VERSION_11


    id("org.springframework.boot") version springVersion
    id("org.jetbrains.kotlin.jvm") version kotlinVersion
    id("org.jetbrains.kotlin.plugin.spring") version kotlinVersion
    id("org.jetbrains.kotlin.plugin.jpa") version kotlinVersion
    id("io.spring.dependency-management") version "1.0.9.RELEASE"
}

val date = Calendar.getInstance()
val current = LocalDateTime.now()
val formatter = DateTimeFormatter.BASIC_ISO_DATE
val timestamp = current.format(formatter)

var versionNumber: String? = "4"//date.get(Calendar.DAY_OF_YEAR).toString()
var minorRelease: String? = date.get(Calendar.MONTH).toString()
var buildNumber: String? = System.getenv("CIRCLE_BUILD_NUM")
var revisionNumber: String? = timestamp
when {
    buildNumber.isNullOrBlank() -> buildNumber = date.get(Calendar.WEEK_OF_YEAR).toString()
}
group = "com.acai"
version = "$versionNumber.$minorRelease.$buildNumber.$revisionNumber"

java.sourceCompatibility = JavaVersion.VERSION_11

tasks.withType<KotlinCompile> {
    kotlinOptions {
        jvmTarget = "11"
        freeCompilerArgs = listOf("-Xjsr305=strict")
    }
}

tasks.withType<Test> {
    useJUnitPlatform()
}

tasks.bootRun {
    args("--spring.profiles.active=cmd")
}

repositories {
    mavenCentral()
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
     
    <changeSet id="$date" author="$user" labels="akilimo">

    </changeSet>
</databaseChangeLog>
"""
    file.writeText(text)
}
dependencies {
    //implementation(fileTree(mapOf("dir" to "libs", "include" to listOf("*.jar"))))

    implementation("org.springframework.boot:spring-boot-starter-web")
    implementation("org.springframework.boot:spring-boot-starter-data-jpa")
    implementation("org.jetbrains.kotlin:kotlin-reflect")
    implementation("org.jetbrains.kotlin:kotlin-stdlib")
    implementation("org.jetbrains.kotlin:kotlin-reflect")
    implementation("com.fasterxml.jackson.module:jackson-module-kotlin")

    implementation("com.h2database:h2")
    implementation("org.liquibase:liquibase-core")
    implementation("org.mariadb.jdbc:mariadb-java-client:2.4.1")

    implementation("io.springfox:springfox-swagger2:2.9.2")
    implementation("io.springfox:springfox-swagger-ui:2.9.2")

    implementation("com.github.rholder.fauxflake:fauxflake-core:1.1.0")
    implementation("com.github.rozidan:modelmapper-spring-boot-starter:1.0.0")

    implementation("joda-time:joda-time:2.10.1")
    implementation("com.googlecode.libphonenumber:libphonenumber:8.10.13")

    implementation("com.infobip:infobip-api-java-client:2.1.0")
    implementation("com.plivo:plivo-java:4.4.1")

    implementation("com.vladmihalcea:hibernate-types-52:1.0.0")

    compileOnly("org.projectlombok:lombok")
    runtimeOnly("org.springframework.boot:spring-boot-devtools")

    //runtimeOnly("com.h2database:h2")

    annotationProcessor("org.springframework.boot:spring-boot-configuration-processor")
    annotationProcessor("org.projectlombok:lombok")


    testImplementation("org.springframework.boot:spring-boot-starter-test") {
        exclude(group = "org.junit.vintage", module = "junit-vintage-engine")
    }

    testImplementation("org.junit.jupiter:junit-jupiter-api")
    testRuntimeOnly("org.junit.jupiter:junit-jupiter-engine")
}
