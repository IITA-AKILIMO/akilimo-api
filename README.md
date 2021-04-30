# AKILIMO backend service

## Akilimo Build status

[![CircleCI](https://circleci.com/gh/masgeek/akilimo-api.svg?style=svg)](https://circleci.com/gh/masgeek/akilimo-api)

**Production branch**

[![CircleCI](https://circleci.com/gh/masgeek/akilimo-api/tree/master.svg?style=svg)](https://circleci.com/gh/masgeek/akilimo-api/tree/master)

**develop branch**

[![CircleCI](https://circleci.com/gh/masgeek/akilimo-api/tree/develop.svg?style=svg)](https://circleci.com/gh/masgeek/akilimo-api/tree/develop)

**quality assesment**

[![codebeat badge](https://codebeat.co/badges/6db0e476-ae3a-40ae-ae80-f42331e9fab9)](https://codebeat.co/projects/github-com-masgeek-akilimo-api-master)


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
$ ./gradlew migrations:generateChangelog  -PchangeName="Name of Changelog"
```

example:

```bash
$ ./gradlew migrations:generateChangelog -PchangeName="Create Users table"
```

The author defaults to the user currently running the command on the system. Optionally, you can use a different author by adding the `-Pauthor`
argument:

```bash
$ ./gradlew migrations:generateChangelog -PchangeName="Create Users table" -Pauthor="The Stig"
```

> Remember to add the changelog file to the `changelog.xml` file

### To override default java version without messing with your machine paths and ENV

Create a file in the root of the project `gradle.properties` then paste your jDK path `org.gradle.java.home=C:\\Program Files\\OpenJDK\\jdk-14.0.2`
Change the path according to your JDK installation

### Changelog generation

> git-chglog -o CHANGELOG.md

### Git Hooks tools

> pre-commit install

> pre-commit uninstall

> pre-commit install --hook-type commit-msg

> pre-commit uninstall --hook-type commit-msg

> pre-commit run --all-files
