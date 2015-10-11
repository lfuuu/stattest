#!/bin/bash
set -e

codeception/bin/db migrate/recreate-db
~/.composer/vendor/bin/codecept run --xml
