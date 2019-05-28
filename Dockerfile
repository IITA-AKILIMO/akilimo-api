#FROM openjdk:8-jdk-alpine
#FROM openjdk:8-jdk-slim
FROM openjdk:11-jre-slim
VOLUME /tmp
LABEL maintainer="barsamms@gmail.com"

ENV SERVER_PORT 8098
ENV SPRING_PROFILES_ACTIVE dev

ENV SPRING_BOOT_USER akilimo
ENV SPRING_BOOT_GROUP akilimo

COPY docker-entrypoint.sh docker-entrypoint.sh

#RUN apk update && apk add bash && apk add curl && rm -rf /var/cache/apk/*

RUN apt-get update && apt-get install -y \
curl

RUN addgroup -S $SPRING_BOOT_USER && adduser -S -g $SPRING_BOOT_GROUP $SPRING_BOOT_USER && \
chmod 555 docker-entrypoint.sh && sh -c 'touch /app.jar'

COPY build/libs/akilimo*.jar /app.jar
COPY src/main/resources/logback-spring.xml /src/main/resources/logback-spring.xml
#COPY src/main/resources/keystore.jks keystore.jks

EXPOSE $SERVER_PORT
ENTRYPOINT ["./docker-entrypoint.sh"]