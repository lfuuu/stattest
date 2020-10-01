#!/bin/sh

cd /opt/stat_rep/stat/config/

#mysql
cp db_stat.php db_stat.local.php
sed -i "s/host=127.0.0.1/host=$MYSQL_HOST/" db_stat.local.php
sed -i "s/dbname=nispd/dbname=$MYSQL_DB/" db_stat.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$MYSQL_USER'/" db_stat.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$MYSQL_PASSWORD'/" db_stat.local.php

#pgsql
cp db_pgsql.local.tpl.php db_pgsql.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_HOST/" db_pgsql.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_DB/" db_pgsql.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_USER'/" db_pgsql.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_PASSWORD'/" db_pgsql.local.php

cp db_pg_slave.local.tpl.php db_pg_slave.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_HOST/" db_pg_slave.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_DB/" db_pg_slave.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_USER'/" db_pg_slave.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_PASSWORD'/" db_pg_slave.local.php

cp db_pg_nnp.local.tpl.php db_pg_nnp.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_HOST/" db_pg_nnp.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_DB/" db_pg_nnp.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_USER'/" db_pg_nnp.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_PASSWORD'/" db_pg_nnp.local.php

cp db_pg_nnp2.local.tpl.php db_pg_nnp2.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_HOST/" db_pg_nnp2.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_DB/" db_pg_nnp2.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_USER'/" db_pg_nnp2.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_PASSWORD'/" db_pg_nnp2.local.php

# Redis
cp cache_redis.local.tpl.php cache_redis.local.php
sed -i "s/'hostname' => 'localhost'/'hostname' => '$REDIS_HOST'/" cache_redis.local.php

# local conf
cd /opt/stat_rep/stat/stat/
sed -i "s/\"AUTOCREATE_VPBX\" => 0/\"AUTOCREATE_VPBX\" => 1/" local.conf.php

#migrations
cd /opt/stat_rep/stat/
#php yii migrate

/usr/sbin/init

echo "##########"
echo "Запуск сервисов"

if [ -f /var/run/crond.pid ]; then
    rm /var/run/crond.pid
fi
/usr/sbin/crond

if [ -f /var/run/nginx.pid ]; then
    rm /var/run/nginx.pid
fi
/usr/sbin/nginx

if [ -f /var/run/php-fpm/php-fpm.pid ]; then
    rm /var/run/php-fpm/php-fpm.pid
fi
/usr/sbin/php-fpm

echo "Сервисы запущены!"

/bin/sleep infinity
