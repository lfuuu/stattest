#!/bin/bash

codeception/bin/db migrate/recreate-db
~/.composer/vendor/bin/codecept run --xml
