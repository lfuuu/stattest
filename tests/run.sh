#!/bin/bash
set -e

codeception/bin/db migrate/recreate-db
rm -f codeception/_output/*
~/.composer/vendor/bin/codecept run unit --xml unit.xml
~/.composer/vendor/bin/codecept run web --xml web.xml
~/.composer/vendor/bin/codecept run func --xml func.xml
