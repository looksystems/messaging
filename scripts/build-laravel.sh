#!/usr/bin/env bash

# Builds a laravel application in the dist folder
# for test purposes only

if [ ! "$LARAVEL_VERSION" ]; then
	LARAVEL_VERSION="^12"
fi

CWD="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT=`dirname "${CWD}"`

if [ ! "$ARTISAN" ]; then
	ARTISAN="./artisan"
fi

if [ ! "$COMPOSER" ]; then
	COMPOSER="composer"
fi

TARGET_PATH="${ROOT}/dist/laravel"

if [ ! -d "${ROOT}/dist" ]; then
	mkdir "${ROOT}/dist"
fi

if [ -d "${TARGET_PATH}" ]; then
	rm -fr "${TARGET_PATH}"
fi

cd `dirname "${TARGET_PATH}"`
BASENAME=`basename "${TARGET_PATH}"`
$COMPOSER create-project --prefer-dist laravel/laravel:$LARAVEL_VERSION $BASENAME

cd "${TARGET_PATH}"

$ARTISAN key:generate

$COMPOSER config minimum-stability dev
$COMPOSER config prefer-stable true
$COMPOSER config platform-check false

$COMPOSER config "repositories.local" path "${ROOT}"
$COMPOSER require "look/messaging"

$ARTISAN migrate:fresh --force
$ARTISAN db:seed --force

cp -f ${ROOT}/src/Laravel/config/* ${TARGET_PATH}/config
cp -f ${ROOT}/src/Laravel/tests/phpunit.xml ${TARGET_PATH}/phpunit.xml
cp -f ${ROOT}/src/Laravel/tests/Feature/* ${TARGET_PATH}/tests/Feature
