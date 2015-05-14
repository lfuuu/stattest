#!/bin/bash
set -e

composer global require "fxp/composer-asset-plugin:~1.0.0"
composer install

exit 0
