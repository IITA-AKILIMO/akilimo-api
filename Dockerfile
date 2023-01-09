# base image to build a JRE
FROM amazoncorretto:17.0.3-alpine as corretto-jdk

# required for strip-debug to work
RUN apk add --no-cache binutils

# Build small JRE image
RUN $JAVA_HOME/bin/jlink \
         --verbose \
         --add-modules ALL-MODULE-PATH \
         --strip-debug \
         --no-man-pages \
         --no-header-files \
         --compress=2 \
         --output /customjre

#RUN apt-get update && apt-get install -y curl && apt-get install -y iputils-ping


FROM alpine:latest

LABEL maintainer="barsamms@gmail.com"

ENV JAVA_HOME=/jre
ENV PATH="${JAVA_HOME}/bin:${PATH}"

ARG USERNAME=akilimo
ARG USER_GROUP=akilimo

COPY api/build/libs/api*.jar app.jar
COPY api/src/main/resources/logback-spring.xml /src/main/resources/logback-spring.xml
COPY api/src/main/resources/ehcache.xml /src/main/resources/ehcache.xml
COPY --from=corretto-jdk /customjre $JAVA_HOME

RUN addgroup -S $USER_GROUP && adduser -S $USERNAME -G $USER_GROUP

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

#USER $USERNAME

EXPOSE 8098

ENTRYPOINT [ "/jre/bin/java", "-jar", "app.jar" ]
