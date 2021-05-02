import org.springframework.boot.gradle.tasks.bundling.BootJar

plugins {
    id("org.springframework.boot")

    kotlin("jvm")
    kotlin("plugin.spring")
}

val mysqlVersion: String by extra

tasks.getByName<BootJar>("bootJar") {
    enabled = false
}

tasks.getByName<Jar>("jar") {
    enabled = true
}

dependencies {
    val springDoc = "1.5.2"

    implementation(fileTree(mapOf("dir" to "libs", "include" to listOf("*.jar"))))


    implementation(project(":enums"))

    implementation(kotlin("reflect"))
    implementation(kotlin("stdlib"))
    implementation(kotlin("stdlib-jdk8"))


    api("org.springdoc:springdoc-openapi-ui:${springDoc}")
    api("org.springdoc:springdoc-openapi-data-rest:${springDoc}")
    api("org.springdoc:springdoc-openapi-kotlin:${springDoc}")


//    api("io.springfox:springfox-swagger2:2.9.2")
//    api("io.springfox:springfox-swagger-ui:2.9.2")


    api("org.springframework.boot:spring-boot-starter-data-jpa")

    implementation("com.h2database:h2")
    implementation("org.mariadb.jdbc:mariadb-java-client:2.2.0")

    implementation("com.vladmihalcea:hibernate-types-52:1.0.0")
}
