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

#global messaging parameters
if [ -z "$MS_WEBHOOK" ]; then
    echo 'No messaging webhook defined'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.web-hook-url=$MS_WEBHOOK"
fi

if [ -z "$MS_TEST_NUMBERS" ]; then
    echo 'No test numbers provided'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.test-numbers=$MS_TEST_NUMBERS"
fi

# infobip configuration
if [ -z "$INFOBIP_ENDPOINT" ]; then
    echo 'No infobip endpoint given'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.infobip.end-point=$INFOBIP_ENDPOINT"
fi

if [ -z "$INFOBIP_USER" ]; then
    echo 'No infobip user given'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.infobip.user-name=$INFOBIP_USER"
fi

if [ -z "$INFOBIP_USER_PASS" ]; then
    echo 'No infobip user pass given'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.infobip.user-pass=$INFOBIP_USER_PASS"
fi


if [ -z "$INFOBIP_AUTH_KEY" ]; then
    echo 'No infobip auth key given'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.infobip.auth-key=$INFOBIP_AUTH_KEY"
fi

if [ -z "$INFOBIP_USER_NAME" ]; then
    echo 'No infobip user name'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.infobip.user-name=$INFOBIP_USER_NAME"
fi

# Plivo setup
if [ -z "$PLIVO_ENDPOINT" ]; then
    echo 'No plivo endpoint given'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.plivo.end-point=$PLIVO_ENDPOINT"
fi

if [ -z "$PLIVO_AUTH_KEY" ]; then
    echo 'No plivo auth key given'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.plivo.auth-key=$PLIVO_AUTH_KEY"
fi

if [ -z "$PLIVO_AUTH_ID" ]; then
    echo 'No plivo authid given'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.plivo.auth-id=$PLIVO_AUTH_ID"
fi

if [ -z "$PLIVO_SENDER" ]; then
    echo 'No plivo sender given'
else
    JAVA_OPTS="$JAVA_OPTS -Dmessaging.plivo.sender=$PLIVO_SENDER"
fi

exec java $JAVA_OPTS \
    -Djavax.net.ssl.trustStorePassword=changeit \
    -Djavax.net.ssl.trustStore=keystore.jks \
    -Djava.security.egd=file:/dev/./urandom \
    -jar \
    /app.jar