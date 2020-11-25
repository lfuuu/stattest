#!/bin/bash

. $(multiwerf use 1.1 stable --as-file)

ENVNAME="prod"
CI_URL="stat.mcnhost.ru"
USE_NGNIX_VIRTUALSERVER="no"
TAG=1.01

werf helm lint --dir ../ --env $ENVNAME --set ci_url=$CI_URL
werf helm render --dir ../  --tag-custom $TAG --env $ENVNAME --set ci_url=$CI_URL ci_dir_home=$CI_DIR_HOME
