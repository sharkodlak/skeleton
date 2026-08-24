#!/bin/bash

cd /app
composer install --no-interaction --no-scripts

php-fpm
