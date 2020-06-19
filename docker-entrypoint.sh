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

if [ -z "$GEN_SQL_STATS" ]; then
  echo 'Hibernate statistics will not be generated'
else
  JAVA_OPTS="$JAVA_OPTS -Dspring.jpa.properties.hibernate.generate_statistics=$GEN_SQL_STATS"
fi

if [ -z "$ORDER_INSERTS" ]; then
  echo 'Hibernate insertions will be ordered for improved performance'
else
  JAVA_OPTS="$JAVA_OPTS -Dspring.jpa.properties.hibernate.order_inserts=$ORDER_INSERTS"
fi

if [ -z "$ORDER_INSERTS" ]; then
  echo 'Using default batch batch_size of 500'
else
  echo "Using batch batch_size of $ORDER_INSERTS"
  JAVA_OPTS="$JAVA_OPTS -Dspring.jpa.properties.hibernate.jdbc.batch_size=$ORDER_INSERTS"
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

if [ -z "$RATE_TYPE" ]; then
  echo 'Defaulting to hourly rate limit type'
else
  echo "Maximum requests per hour is $RATE_TYPE"
  JAVA_OPTS="$JAVA_OPTS -Drate.limit.rate-type=$RATE_TYPE"
fi

if [ -z "$ENABLE_RATE_LIMIT" ]; then
  echo 'Rate limiting is enable by default'
else
  JAVA_OPTS="$JAVA_OPTS -Drate.limit.enabled=$ENABLE_RATE_LIMIT"
fi

if [ -z "$RATE_LIMIT" ]; then
  echo 'Using default rate limit ceiling of 1'
else
  echo "Maximum requests per $RATE_TYPE is $RATE_LIMIT"
  JAVA_OPTS="$JAVA_OPTS -Drate.limit.max-request=$RATE_LIMIT"
fi

#global messaging parameters
if [ -z "$SMS_BASE_URL" ]; then
  echo 'Using default base url'
else
  JAVA_OPTS="$JAVA_OPTS -Dmessaging.sms.base-url=$SMS_BASE_URL"
fi

if [ -z "$SMS_ENDPONT" ]; then
  echo 'Using default end point'
else
  JAVA_OPTS="$JAVA_OPTS -Dmessaging.sms.end-point=$SMS_ENDPONT"
fi

if [ -z "$SMS_USER" ]; then
  echo 'No sms user defined'
else
  JAVA_OPTS="$JAVA_OPTS -Dmessaging.sms.sms-user=$SMS_USER"
fi

if [ -z "$SMS_TOKEN" ]; then
  echo 'No sms token defined'
else
  JAVA_OPTS="$JAVA_OPTS -Dmessaging.sms.sms-token=$SMS_TOKEN"
fi

if [ -z "$BRANDED_CODES" ]; then
  echo 'No branded codes defined using default list'
else
  JAVA_OPTS="$JAVA_OPTS -Dmessaging.sms.branded-codes=$BRANDED_CODES"
fi

exec java $JAVA_OPTS \
-jar \
/app.jar
