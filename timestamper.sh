#!/bin/bash

read -rp "Enter migration file name: " migrationName

# Define a timestamp function
cd src/main/resources/liquibase/changelog || exit
#timestamp=$( date +%s)
# shellcheck disable=SC2034
timestamp=$(date +%Y%m%d%H%M%S)

filename="${timestamp}_${migrationName}.xml"

look=$(whoami)
echo "$timestamp $look" >"$filename"
