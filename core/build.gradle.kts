import org.springframework.boot.gradle.tasks.bundling.BootJar

plugins {
    id("org.springframework.boot")
    kotlin("jvm")
    kotlin("plugin.spring")
}

tasks.getByName<BootJar>("bootJar") {
    enabled = false
}

tasks.getByName<Jar>("jar") {
    enabled = true
}

dependencies {
    val coroutinesVersion = "1.5.2-native-mt"

    implementation(project(":config"))
    implementation(project(":enums"))
    implementation(project(":database"))

    implementation(kotlin("reflect"))
    implementation(kotlin("stdlib"))
    implementation(kotlin("stdlib-jdk8"))

    implementation("org.springframework.boot:spring-boot-starter-web")

    api("org.springframework.boot:spring-boot-starter-data-jpa")
    api("org.springframework.boot:spring-boot-starter-security")
//    api("org.springframework.security:spring-security-crypto")
    api("org.springframework.boot:spring-boot-starter-validation")

    api("org.jetbrains.kotlinx:kotlinx-coroutines-core:${coroutinesVersion}")
    api("org.jetbrains.kotlinx:kotlinx-coroutines-reactor:${coroutinesVersion}")

    api("com.fasterxml.jackson.module:jackson-module-kotlin:2.14.0")
    api("commons-codec:commons-codec:1.15")
    api("com.github.rozidan:modelmapper-spring-boot-starter:2.3.1")
    api("joda-time:joda-time:2.12.2")

    implementation("com.github.rholder.fauxflake:fauxflake-core:1.1.0")
    implementation("com.googlecode.libphonenumber:libphonenumber:8.13.3")
    implementation("org.apache.commons:commons-collections4:4.4")
    implementation("org.apache.commons:commons-csv:1.9.0")
    //implementation("com.opencsv:opencsv:4.6")

    testImplementation("org.junit.jupiter:junit-jupiter-api")
    testRuntimeOnly("org.junit.jupiter:junit-jupiter-engine")
}
