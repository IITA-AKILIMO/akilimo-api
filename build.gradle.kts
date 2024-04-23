import org.jetbrains.kotlin.gradle.tasks.KotlinCompile
import java.util.*

buildscript {
    repositories {
        mavenCentral()
        mavenLocal()
        jcenter()
    }
}

plugins {
    val kotlinVersion = "1.6.21"
    val springVersion = "2.7.7"

    id("org.springframework.boot") version springVersion apply false
    id("io.spring.dependency-management") version "1.0.15.RELEASE" apply false

    kotlin("jvm") version kotlinVersion apply false
    kotlin("plugin.spring") version kotlinVersion apply false
    kotlin("plugin.jpa") version kotlinVersion apply false
}


allprojects {
    val date = Calendar.getInstance()
    val versionNumber: Int = 7
    val minorRelease: Int = 2
    val bugRelease: String = "0"
    var buildNumber: String? = System.getenv("CIRCLE_BUILD_NUM")
    when {
        buildNumber.isNullOrBlank() -> buildNumber = date.get(Calendar.DAY_OF_YEAR).toString()
    }
    group = "com.iita"
    version = "$versionNumber.$minorRelease.$bugRelease-build-$buildNumber"

    tasks.withType<JavaCompile> {
        sourceCompatibility = "17"
        targetCompatibility = "17"
    }

    tasks.withType<KotlinCompile> {
        kotlinOptions {
            freeCompilerArgs = listOf("-Xjsr305=strict")
            jvmTarget = "17"
        }
    }

    tasks.withType<Test> {
        useJUnitPlatform()
    }
}

repositories {
    mavenCentral()
}

subprojects {
    repositories {
        mavenCentral()
        mavenLocal()
        maven {
            url = uri("https://jitpack.io")
        }
    }
    apply {
        plugin("io.spring.dependency-management")
    }
}
