#!/bin/bash
set -e

add-apt-repository -y ppa:ondrej/php5
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -
echo 'deb http://apt.postgresql.org/pub/repos/apt/ trusty-pgdg main' > /etc/apt/sources.list.d/pgdg.list
wget --quiet -O - http://nginx.org/keys/nginx_signing.key | apt-key add -
echo 'deb http://nginx.org/packages/mainline/ubuntu/ trusty nginx' > /etc/apt/sources.list.d/nginx.list
apt-get update
apt-get install -y mc git
export DEBIAN_FRONTEND=noninteractive
apt-get install locales && dpkg-reconfigure locales
echo 'LANG="en_US.UTF-8"' > /etc/default/locale
echo 'LANGUAGE="en_US.UTF-8"' >> /etc/default/locale
echo 'LC_ALL="en_US.UTF-8"' >> /etc/default/locale

sudo apt-get install -y nginx mysql-server postgresql-9.4
sudo apt-get install -y php5 php5-fpm php5-cli php5-curl php5-gd php5-json php5-mcrypt php5-mysqlnd php5-pgsql php5-readline php5-xdebug php5-xmlrpc php5-intl
cp ./nginx.conf /etc/nginx/nginx.conf
cp ./nginx.default.conf /etc/nginx/conf.d/default.conf
cp ./php.ini /etc/php5/fpm/php.ini
cp ./php.ini /etc/php5/cli/php.ini
cp ./php-fpm.www.conf /etc/php5/fpm/pool.d/www.conf

restart php5-fpm
service nginx restart

sed 's/127.0.0.1/0.0.0.0/g' /etc/mysql/my.cnf > /etc/mysql/my.cnf.tmp
mv /etc/mysql/my.cnf.tmp /etc/mysql/my.cnf
service mysql restart

sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/g" /etc/postgresql/9.4/main/postgresql.conf
echo "local   all             all                           peer" > /etc/postgresql/9.4/main/pg_hba.conf
echo "host    all             all             all           md5" >> /etc/postgresql/9.4/main/pg_hba.conf
service postgresql restart

echo "create database if not exists nispd" | mysql
echo "create database if not exists ats" | mysql
echo "create database if not exists ats2" | mysql

echo "create user 'vagrant'@'%'" | mysql
echo "create user 'stat_operator'@'localhost'" | mysql
echo "set password for 'vagrant'@'%' = password('vagrant')" | mysql
echo "grant all privileges on * . * TO 'latyntsev'@'localhost'" | mysql
echo "grant all privileges on * . * TO 'vagrant'@'%'" | mysql
echo "grant all privileges on * . * TO 'stat_operator'@'localhost'" | mysql
echo "flush privileges" | mysql

echo "CREATE DATABASE voipdb " | su -c psql postgres
echo "CREATE USER vagrant WITH PASSWORD 'vagrant' " | su -c psql postgres
echo "grant all privileges on database voipdb to vagrant" | su -c psql postgres

curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

exit 0
