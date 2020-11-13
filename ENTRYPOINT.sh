#!/bin/sh

DIR_HOME="/opt/stat_rep"
DIR_STAT="$DIR_HOME/stat"

cd $DIR_STAT/config/

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

# Modules

# Socket
cd $DIR_STAT/modules/socket/config/
cp params.php params.local.php
sed -i "s/'url' => ''/'url' => '$SOCKET_URL'/" params.local.php
sed -i "s/'secretKey' => ''/'secretKey' => '$SOCKET_SECRET'/" params.local.php

# WebHook
cd $DIR_STAT/modules/webhook/config/
cp params.php params.local.php
sed -i "s/'secretKey' => ''/'secretKey' => '$WEB_HOOK_SECRET'/" params.local.php
sed -i "s/'token' => ''/'token' => '$WEB_HOOK_TOKEN'/" params.local.php
sed -i "s/'account_id' => ''/'account_id' => '$WEB_HOOK_ACCOUNT_ID'/" params.local.php
sed -i "s/'vpbx_id' => ''/'vpbx_id' => '$WEB_HOOK_VPBX_ID'/" params.local.php

# Payments
cd $DIR_STAT/modules/payments/config/
cp params.php params.local.php
sed -i "s/'user' => ''/'user' => '$PAYMENTS_USER'/" params.local.php
sed -i "s/'password' => ''/'password' => '$PAYMENTS_PASSWORD'/" params.local.php
sed -i "s/'publishable_key' => ''/'publishable_key' => '$PAYMENTS_PUBLISHABLE_KEY'/" params.local.php
sed -i "s/'secret_key' => ''/'secret_key' => '$PAYMENTS_SECRET'/" params.local.php

# NNP
cd $DIR_STAT/modules/nnp/config/
cp params.php params.local.php
sed -i "s/'numlex_user' => ''/'numlex_user' => '$NNP_NUMLEX_USER'/" params.local.php
sed -i "s/'numlex_pass' => ''/'numlex_pass' => '$NNP_NUMLEX_PASS'/" params.local.php

# SBIS
cd $DIR_STAT/modules/sbisTenzor/config/
cp params.php params.local.php
sed -i "s/'login' => ''/'login' => '$SBIS_LOGIN'/" params.local.php
sed -i "s/'password' => ''/'password' => '$SBIS_PASSWORD'/" params.local.php

# local conf
cd $DIR_STAT/stat/
sed -i "s/\"AUTOCREATE_VPBX\" => 0/\"AUTOCREATE_VPBX\" => 1/" local.conf.php

#migrations
cd $DIR_STAT
php yii migrate

echo "Configs are ready!"
