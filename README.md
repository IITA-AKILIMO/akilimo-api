# AKILIMO API

The AKILIMO API is a Standardized RESTful Web Service API Specification that allows for the computing and retrieval of recommendations data for various agronomic practices such as Fertilizer application, Intercropping, scheduled planting and harvesting and best planting practices.

### The AKILIMO API is divided into three sections

#### 1. Advanced

This takes the greatest number of inputs and is suitable for applications where user input is not limited such as mobile applications and web interfaces.

#### 2. Intermediate

This version takes moderately less inputs and is suitable in usages that requires moderate amount of data to be provided by the user, such scenarios include chatbot applications and survey style applications.

#### 3. Basic

The basic version only takes the minimal amount of information required to provide suitable recommendation, it is suitable for usages that do not require extensive data from the end user such as USSD applications, Integrated voice services

## Getting started

To get you quickly get you started on enjoying the integration process of our REST APIs. Follow the quick steps below:
1.	You will need to register and get access to the API you can do so via the [user portal], you will then be provided with an API key
2.	Refer to the API documentation on both [GitHub] and [Swagger]
3.	Decide which kind of API you want to consume based on your project needs
4.	Integrate your choice of API version into your system(s) and enjoy AKILIMO recommendations

---

## Versions
``` to be updated ```

---
## Akilimo Build status

[![CircleCI](https://circleci.com/gh/IITA-AKILIMO/akilimo-api.svg?style=svg)](https://circleci.com/gh/IITA-AKILIMO/akilimo-api)

**Production branch**

[![CircleCI](https://circleci.com/gh/IITA-AKILIMO/akilimo-api/tree/main.svg?style=svg)](https://circleci.com/gh/IITA-AKILIMO/akilimo-api/tree/main)

**develop branch**

[![CircleCI](https://circleci.com/gh/IITA-AKILIMO/akilimo-api/tree/develop.svg?style=svg)](https://circleci.com/gh/IITA-AKILIMO/akilimo-api/tree/develop)

**quality assesment**

[![codebeat badge](https://codebeat.co/badges/c0cb5198-13b9-46dc-b83e-79f8989c1698)](https://codebeat.co/projects/github-com-iita-akilimo-akilimo-api-develop)


# Development steps


## Dependencies

- JDK 14

## Tools used

- Gradle
- Kotlin

---

## Setup IntelliJ

Install Spring assistant to help in springboot config processing and code autocompletion
[**Install Spring Assistant**](https://plugins.jetbrains.com/plugin/10229-spring-assistant/)
option.

## Environment variables

To switch the environment in dev mode adjust these basic parameters

```
SPRING_PROFILES_ACTIVE=uat;SPRING_DATASOURCE_URL=jdbc:postgresql://localhost:5432/postgres;SPRING_DATASOURCE_USERNAME=user;SPRING_DATASOURCE_PASSWORD=pass
```

### Generate Liquibase changelog file

```bash
./gradlew migrations:generateChangelog  -PchangeName="Name of Changelog"
```

example:

```bash
./gradlew migrations:generateChangelog -PchangeName="Create Users table"
```

The author defaults to the user currently running the command on the system. Optionally, you can use a different author by adding the `-Pauthor`
argument:

```bash
./gradlew migrations:generateChangelog -PchangeName="Create Users table" -Pauthor="The Stig"
```

> Remember to add the changelog file to the `changelog.xml` file


### Generate Swagger json file

```bash
./gradlew :api:generateOpenApiDocs
```


## Other tools

### Generate api HTML
```bash
npm install --location=global redoc-cli
````
```bash
redoc-cli bundle -o .\docs\index.html .\docs\akilimo.json
```

### Generate markdown files

```bash
npm install --location=global openapi-to-md
````
```bash
openapi-to-md .\docs\akilimo.json > .\docs\API.MD
```


### To override default java version without messing with your machine paths and ENV

Create a file in the root of the project `gradle.properties` then paste your jDK path `org.gradle.java.home=C:\\Program Files\\OpenJDK\\jdk-14.0.2`
Change the path according to your JDK installation

### Changelog generation
```bash
git-chglog -o CHANGELOG.md
```

### Git Hooks

```bash
pre-commit install
```
```bash
pre-commit uninstall
```
```bash
pre-commit install --hook-type commit-msg
```
```bash
pre-commit uninstall --hook-type commit-msg
```
```bash
pre-commit run --all-files
```
