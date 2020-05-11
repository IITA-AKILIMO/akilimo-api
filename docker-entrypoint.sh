#!/usr/bin/env bash

if [ -z "$DEBUG" ]; then
  echo 'Debugging is disable dby default'
else
  JAVA_OPTS="$JAVA_OPTS -Ddebug=$DEBUG"
fi

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
  JAVA_OPTS="$JAVA_OPTS -Dcom.sun.net.ssl.checkRevocation=$VERIFY_CERT"
fi
#currency rates
if [ -z "$NGN_USD_RATE" ]; then
  echo 'No Nigerian exchange rate given'
else
  JAVA_OPTS="$JAVA_OPTS -Dakilimo.currency.ngn-usd-rate=$NGN_USD_RATE"
fi

if [ -z "$TZS_USD_RATE" ]; then
  echo 'No Tanzanian exchange rate given'
else
  JAVA_OPTS="$JAVA_OPTS -Dakilimo.currency.tzs-usd-rate=$TZS_USD_RATE"
fi

if [ -z "$KES_USD_RATE" ]; then
  echo 'No Kenyan exchange rate given'
else
  JAVA_OPTS="$JAVA_OPTS -Dakilimo.currency.kes-usd-rate=$KES_USD_RATE"
fi

# plumber resources
if [ -z "$PLUMBER_ENDPOINT" ]; then
  echo 'No plumber endpoint given'
else
  JAVA_OPTS="$JAVA_OPTS -Dplumber.base-url=$PLUMBER_ENDPOINT"
fi

if [ -z "$PLUMBER_DEV_ENDPOINT" ]; then
  echo 'No plumber dev endpoint given'
else
  JAVA_OPTS="$JAVA_OPTS -Dplumber.dev-url=$PLUMBER_DEV_ENDPOINT"
fi

if [ -z "$DEMO_MODE" ]; then
  echo 'No demo flag given defaulting to false'
else
  JAVA_OPTS="$JAVA_OPTS -Dplumber.demo-mode=$DEMO_MODE"
fi

if [ -z "$TZ_DEMO_ENDPOINT" ]; then
  echo 'No tanzanian demo endpoint given'
else
  JAVA_OPTS="$JAVA_OPTS -Dplumber.recommendation-tz-demo=$TZ_DEMO_ENDPOINT"
fi

if [ -z "$NG_DEMO_ENDPOINT" ]; then
  echo 'No nigerian demo endpoint given'
else
  JAVA_OPTS="$JAVA_OPTS -Dplumber.recommendation-ng-demo=$NG_DEMO_ENDPOINT"
fi

if [ -z "$TZ_ENDPOINT" ]; then
  echo 'No tanzanian endpoint given'
else
  JAVA_OPTS="$JAVA_OPTS -Dplumber.recommendation-tz=$TZ_ENDPOINT"
fi

if [ -z "$NG_ENDPOINT" ]; then
  echo 'No nigerian endpoint given'
else
  JAVA_OPTS="$JAVA_OPTS -Dplumber.recommendation-ng=$NG_ENDPOINT"
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

if [ -z "$INFOBIP_USER_NAME" ]; then
  echo 'No infobip user name'
else
  JAVA_OPTS="$JAVA_OPTS -Dmessaging.infobip.user-name=$INFOBIP_USER_NAME"
fi

if [ -z "$INFOBIP_USER_PASS" ]; then
  echo 'No infobip user pass given'
else
  JAVA_OPTS="$JAVA_OPTS -Dmessaging.infobip.user-pass=$INFOBIP_USER_PASS"
fi

if [ -z "$INFOBIP_SENDER" ]; then
  echo 'No infobip sender given'
else
  JAVA_OPTS="$JAVA_OPTS -Dmessaging.infobip.sender=$INFOBIP_SENDER"
fi

if [ -z "$INFOBIP_AUTH_KEY" ]; then
  echo 'No infobip auth key given'
else
  JAVA_OPTS="$JAVA_OPTS -Dmessaging.infobip.auth-key=$INFOBIP_AUTH_KEY"
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
-jar \
/app.jar
