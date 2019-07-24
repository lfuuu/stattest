#!/bin/bash
set -e

codeception/bin/db migrate/recreate-db
rm -rf codeception/_output/*

## for coverage report xdebug needed
#~/.composer/vendor/bin/codecept run unit --xml unit.xml --coverage --coverage-xml --coverage-html
~/.composer/vendor/bin/codecept run unit --xml unit.xml
~/.composer/vendor/bin/codecept run web --xml web.xml
~/.composer/vendor/bin/codecept run func --xml func.xml
