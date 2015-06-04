#!/bin/bash
set -e

add-apt-repository -y ppa:ondrej/php5
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -
echo 'deb http://apt.postgresql.org/pub/repos/apt/ trusty-pgdg main' > /etc/apt/sources.list.d/pgdg.list
apt-get update
apt-get install -y mc git
export DEBIAN_FRONTEND=noninteractive
apt-get install -y apache2 mysql-server postgresql-9.4
apt-get install -y php5 php5-cli php5-curl php5-gd php5-json php5-mcrypt php5-mysqlnd php5-pgsql php5-readline php5-xdebug php5-xmlrpc

cp ./apache.default.conf /etc/apache2/sites-available/000-default.conf
cp ./php.ini /etc/php5/apache2/php.ini
cp ./php.ini /etc/php5/cli/php.ini
sed 's/www-data/vagrant/g' /etc/apache2/envvars > /etc/apache2/envvars.tmp
mv /etc/apache2/envvars.tmp /etc/apache2/envvars
a2enmod rewrite
service apache2 restart

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
echo "set password for 'vagrant'@'%' = password('vagrant')" | mysql
echo "grant all privileges on * . * TO 'latyntsev'@'localhost'" | mysql
echo "grant all privileges on * . * TO 'vagrant'@'%'" | mysql
echo "flush privileges" | mysql

echo "CREATE DATABASE voipdb " | su -c psql postgres
echo "CREATE USER vagrant WITH PASSWORD 'vagrant' " | su -c psql postgres
echo "grant all privileges on database voipdb to vagrant" | su -c psql postgres

curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

exit 0
