#!/usr/bin/env bash

if [ -z "$CROP_DATABASE_URL" ]; then
    echo 'Default database URL used'
else
    JAVA_OPTS="$JAVA_OPTS -Dspring.datasource.url=$CROP_DATABASE_URL"
fi

if [ -z "$CROP_DATABASE_USERNAME" ]; then
    echo 'Default database username used'
else
    JAVA_OPTS="$JAVA_OPTS -Dspring.datasource.username=$CROP_DATABASE_USERNAME"
fi

if [ -z "$CROP_DATABASE_PASSWORD" ]; then
    echo 'Default database password used'
else
    JAVA_OPTS="$JAVA_OPTS -Dspring.datasource.password=$CROP_DATABASE_PASSWORD"
fi

if [ -z "$VERIFY_CERT" ]; then
    echo 'Certificate will be verified'
else
    JAVA_OPTS="$JAVA_OPTS -Dcom.sun.net.ssl.checkRevocation=false=$VERIFY_CERT"
fi

if [ -z "$PLUMBER_ENDPOINT" ]; then
    echo 'No plumber endpoint given'
else
    JAVA_OPTS="$JAVA_OPTS -Dplumber.endpoint=$PLUMBER_ENDPOINT"
fi

exec java $JAVA_OPTS \
    -Djavax.net.ssl.trustStorePassword=changeit \
    #-Djavax.net.ssl.trustStore=keystore.jks \
    -Djava.security.egd=file:/dev/./urandom \
    -jar \
    /app.jar