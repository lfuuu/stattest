#!/bin/bash
set -e

apt-get update
apt-get install -y mc git
export DEBIAN_FRONTEND=noninteractive
apt-get install -y apache2 mysql-server
add-apt-repository -y ppa:ondrej/php5
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


echo "create database if not exists nispd" | mysql
echo "create database if not exists ats" | mysql
echo "create database if not exists ats2" | mysql

echo "create user 'vagrant'@'%'" | mysql
echo "set password for 'vagrant'@'%' = password('vagrant')" | mysql
echo "grant all privileges on * . * TO 'latyntsev'@'localhost'" | mysql
echo "grant all privileges on * . * TO 'vagrant'@'%'" | mysql
echo "flush privileges" | mysql


curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

exit 0
