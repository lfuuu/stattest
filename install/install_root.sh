#!/bin/bash
set -e

apt-get update
apt-get install -y mc git
export DEBIAN_FRONTEND=noninteractive
apt-get install -y apache2 mysql-server
apt-get install -y php5 php5-curl php5-mcrypt php5-gd php5-mysqlnd php5-pgsql

pwd
cp ./apache.default.conf /etc/apache2/sites-available/000-default.conf
cp ./php.ini /etc/php5/apache2/php.ini
cp ./php.ini /etc/php5/cli/php.ini
ln -fs /etc/php5/mods-available/mcrypt.ini /etc/php5/apache2/conf.d/mcrypt.ini
ln -fs /etc/php5/mods-available/mcrypt.ini /etc/php5/cli/conf.d/mcrypt.ini
sed 's/www-data/vagrant/g' /etc/apache2/envvars > /etc/apache2/envvars2
mv /etc/apache2/envvars2 /etc/apache2/envvars
a2enmod rewrite

service apache2 restart

echo "create database if not exists nispd" | mysql
echo "create user 'latyntsev'@'localhost'" | mysql
echo "GRANT ALL PRIVILEGES ON * . * TO 'latyntsev'@'localhost'" | mysql
echo "FLUSH PRIVILEGES" | mysql


curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

exit 0
