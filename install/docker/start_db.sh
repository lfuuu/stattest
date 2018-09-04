#!/bin/bash

localedef  -i ru_RU -f UTF-8 ru_RU.UTF-8

#sed -i "/\[mysqld\]/a collation-server = utf8_unicode_ci\ninit-connect='SET NAMES utf8'\ncharacter-set-server = utf8\ncharacter_set_results = utf8\ncharacter_set_connection = utf8\ncharacter_set_client = utf8" /etc/my.cnf

#sed -i "/\[mysqld\]/a init-connect='SET NAMES utf8'" /etc/my.cnf
#echo "[client]" >> /etc/my.cnf
#echo "default-character-set=utf8" >> /etc/my.cnf

systemctl restart mysql

sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/g" data/postgresql.conf
echo "local   all             all                           peer" > data/pg_hba.conf
echo "host    all             all             all           md5" >> data/pg_hba.conf
echo -e "\n127.0.0.1:*:*:stat:stat" >> /root/.pgpass


su -c "/usr/pgsql-9.4/bin/pg_ctl -D data restart" postgres >> /dev/null
