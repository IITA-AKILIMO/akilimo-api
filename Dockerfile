
FROM openjdk:16-slim-buster

LABEL maintainer="barsamms@gmail.com"

ENV SERVER_PORT 8098
ENV SPRING_BOOT_USER akilimo
ENV SPRING_BOOT_GROUP akilimo

ENV TZ=Africa/Nairobi

#ENV JAVA_OPTS="$JAVA_OPTS -Dcom.sun.net.ssl.checkRevocation=$VERIFY_CERT"

RUN apt-get update && apt-get install -y curl && apt-get install -y iputils-ping


RUN useradd $SPRING_BOOT_USER && usermod -aG $SPRING_BOOT_GROUP $SPRING_BOOT_USER && sh -c 'touch /app.jar'

COPY api/build/libs/api*.jar /app.jar
COPY api/src/main/resources/logback-spring.xml /src/main/resources/logback-spring.xml


RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

EXPOSE $SERVER_PORT

ENTRYPOINT ["java", "-jar", "app.jar"]
