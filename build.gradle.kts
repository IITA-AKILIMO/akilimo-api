import org.jetbrains.kotlin.gradle.tasks.KotlinCompile

plugins {
	val kotlinVersion = "1.2.71"
	val springVersion = "2.1.4.RELEASE"

	val javaVersion = JavaVersion.VERSION_11


	id("org.springframework.boot") version springVersion
	id("org.jetbrains.kotlin.jvm") version kotlinVersion
	id("org.jetbrains.kotlin.plugin.spring") version kotlinVersion
	id("org.jetbrains.kotlin.plugin.jpa") version kotlinVersion
	id("io.spring.dependency-management") version "1.0.7.RELEASE"
}

group = "com.acai"
version = "1.0.0"

tasks.withType<KotlinCompile> {
	kotlinOptions {
		jvmTarget = "1.8"
		freeCompilerArgs = listOf("-Xjsr305=strict")
	}
}

tasks.withType<Test> {
	useJUnitPlatform()
}

repositories {
	mavenCentral()
}

dependencies {
	implementation(fileTree(mapOf("dir" to "libs", "include" to listOf("*.jar"))))

	implementation("org.springframework.boot:spring-boot-starter-web")
	implementation("org.springframework.boot:spring-boot-starter-data-jpa")
	implementation("org.jetbrains.kotlin:kotlin-reflect")
	implementation("org.jetbrains.kotlin:kotlin-stdlib")
	implementation("org.jetbrains.kotlin:kotlin-reflect")
	implementation("com.fasterxml.jackson.module:jackson-module-kotlin")

	implementation("com.h2database:h2")
	implementation ("org.liquibase:liquibase-core")
	implementation ("org.mariadb.jdbc:mariadb-java-client:2.4.1")

	implementation ("io.springfox:springfox-swagger2:2.9.2")
	implementation ("io.springfox:springfox-swagger-ui:2.9.2")

	implementation ("com.github.rholder.fauxflake:fauxflake-core:1.1.0")
	implementation("com.github.rozidan:modelmapper-spring-boot-starter:1.0.0")

	implementation("joda-time:joda-time:2.10.1")

	compileOnly("org.projectlombok:lombok")
	runtimeOnly("org.springframework.boot:spring-boot-devtools")
	//runtimeOnly("com.h2database:h2")

	annotationProcessor("org.springframework.boot:spring-boot-configuration-processor")
	annotationProcessor("org.projectlombok:lombok")

	testCompile("org.springframework.boot:spring-boot-starter-test") {
		//exclude(module = "junit")
	}

	testImplementation("org.junit.jupiter:junit-jupiter-api")
	testRuntimeOnly("org.junit.jupiter:junit-jupiter-engine")
}
