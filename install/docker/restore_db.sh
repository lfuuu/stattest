#!/bin/bash

status=1
while [ $status -ne 0 ]
do
	echo "select 1" | mysql
	status=$?
done

status=1
while [ $status -ne 0 ]
do
        echo "select 1 " | su -c psql postgres
        status=$?
done

echo "create database if not exists nispd DEFAULT CHARACTER SET utf8" | mysql
echo "create database if not exists nispd_test DEFAULT CHARACTER SET utf8" | mysql
echo "create database if not exists ats DEFAULT CHARACTER SET utf8" | mysql
echo "create database if not exists ats2 DEFAULT CHARACTER SET utf8" | mysql

echo "create user 'stat'@'localhost'" | mysql
echo "create user 'stat_operator'@'localhost'" | mysql
echo "create user 'latyntsev'@'localhost'" | mysql
echo "set password for 'stat'@'localhost' = password('stat')" | mysql
echo "grant all privileges on * . * TO 'stat'@'localhost'" | mysql
echo "grant all privileges on * . * TO 'stat_operator'@'localhost'" | mysql
echo "grant all privileges on * . * TO 'latyntsev'@'localhost'" | mysql
echo "flush privileges" | mysql

echo "source /opt/stat_rep/stat/migrations/migrations/data/m100000_000001_init/nispd.sql" | mysql nispd
echo "source /opt/stat_rep/stat/migrations/migrations/data/m100000_000001_init/nispd.sql" | mysql nispd_test

echo "CREATE DATABASE voipdb ENCODING 'UTF8' LC_COLLATE = 'ru_RU.UTF-8' LC_CTYPE = 'ru_RU.UTF-8' TEMPLATE = template0" | su -c psql postgres
echo "CREATE USER stat WITH PASSWORD 'stat' " | su -c psql postgres
echo "grant all privileges on database voipdb to stat" | su -c psql postgres

echo "CREATE DATABASE nispd ENCODING 'UTF8' LC_COLLATE = 'ru_RU.UTF-8' LC_CTYPE = 'ru_RU.UTF-8' TEMPLATE = template0" | su -c psql postgres
echo "CREATE DATABASE nispd_test ENCODING 'UTF8' LC_COLLATE = 'ru_RU.UTF-8' LC_CTYPE = 'ru_RU.UTF-8' TEMPLATE = template0" | su -c psql postgres
echo "create schema calls_raw" | su -c "psql -d nispd" postgres
echo "create schema billing" | su -c "psql -d nispd" postgres
echo "create schema nnp" | su -c "psql -d nispd" postgres
echo "create schema billing_uu" | su -c "psql -d nispd" postgres
echo "grant ALL PRIVILEGES on schema calls_raw to stat" | su -c "psql -d nispd" postgres
echo "grant ALL PRIVILEGES on schema billing to stat" | su -c "psql -d nispd" postgres
echo "grant ALL PRIVILEGES on schema nnp to stat" | su -c "psql -d nispd" postgres
echo "grant ALL PRIVILEGES on schema billing_uu to stat" | su -c "psql -d nispd" postgres

pg_dump -h 85.94.32.228 -U pgsqltest -n calls_raw -v -s -T calls_raw -f calls_raw_schema.sql nispd
psql --set ON_ERROR_STOP=off -h 127.0.0.1 -U stat -f calls_raw_schema.sql nispd
pg_dump -h 85.94.32.228 -U pgsqltest -n billing -v -s -T cached_counters -f cached_counters_schema.sql nispd
psql --set ON_ERROR_STOP=off -h 127.0.0.1 -U stat -f cached_counters_schema.sql nispd
pg_dump -h 85.94.32.228 -U pgsqltest -n nnp -v -s -f nnp_schema.sql nispd
psql --set ON_ERROR_STOP=off -h 127.0.0.1 -U stat -f nnp_schema.sql nispd
pg_dump -h 85.94.32.228 -U pgsqltest -a -t nnp.status -f nnp_status.sql nispd
psql -h 127.0.0.1 -U stat -f nnp_status.sql nispd
pg_dump -h 85.94.32.228 -U pgsqltest -a -t nnp.ndc_type -f nnp_ndc_type.sql nispd
psql -h 127.0.0.1 -U stat -f nnp_ndc_type.sql nispd
pg_dump -h 85.94.32.228 -U pgsqltest -a -t nnp.land -f nnp_land.sql nispd
psql -h 127.0.0.1 -U stat -f nnp_land.sql nispd
pg_dump -h 85.94.32.228 -U pgsqltest -a -t nnp.country -f nnp_country.sql nispd
psql -h 127.0.0.1 -U stat -f nnp_country.sql nispd
pg_dump -h 85.94.32.228 -U pgsqltest -n billing_uu -v -s -f billing_uu_schema.sql nispd
psql -h 127.0.0.1 -U stat -f billing_uu_schema.sql nispd
