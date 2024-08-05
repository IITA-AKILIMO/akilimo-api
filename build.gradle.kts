import org.jetbrains.kotlin.gradle.dsl.JvmTarget
import org.jetbrains.kotlin.gradle.tasks.KotlinCompile
import java.util.*

buildscript {
    repositories {
        mavenCentral()
        mavenLocal()
        maven {
            url = uri("https://plugins.gradle.org/m2/")
        }
    }
}

plugins {
    alias(libs.plugins.springBoot) apply false
    alias(libs.plugins.dependencyManagement) apply false
    alias(libs.plugins.kotlinJvm) apply false
    alias(libs.plugins.kotlinSpring) apply false
    alias(libs.plugins.kotlinJpa) apply false
    alias(libs.plugins.dependencyUpdates) apply true
}

allprojects {
    val date = Calendar.getInstance()
    var buildNumber: String? = System.getenv("CIRCLE_BUILD_NUM")
    when {
        buildNumber.isNullOrBlank() -> buildNumber = date.get(Calendar.DAY_OF_YEAR).toString()
    }
    group = "com.iita"
    version = "1.0.0-build-$buildNumber"


    tasks.withType<KotlinCompile> {
        compilerOptions.jvmTarget.set(JvmTarget.JVM_21)
    }

    tasks.withType<Test> {
        useJUnitPlatform()
    }
}

subprojects {
    repositories {
        mavenCentral()
        mavenLocal()
        maven {
            url = uri("https://jitpack.io")
        }
        maven {
            url = uri("https://plugins.gradle.org/m2/")
        }
    }
    apply {
        plugin("io.spring.dependency-management")
    }
}
