#!/bin/sh

host_name_cryptopro=$CRYPTOPRO_HOST
ip_cryptopro=$(getent hosts $host_name_cryptopro | awk '{ print $1 }')

# ssh for cryptopro, root
cp -rp /root/id_rsa /root/.ssh
cp -rp /root/known_hosts /root/.ssh
chmod 0600 /root/.ssh/id_rsa
chmod u+w /root/.ssh/known_hosts
sed -ri "s/cryptopro-prod,10.105.196.57/$host_name_cryptopro,$ip_cryptopro/" /root/.ssh/known_hosts

# first run to test with root
ssh cryptopro-prod "/opt/cprocsp/sbin/amd64/cpconfig -license -view"

# ssh for cryptopro, www-data
cp -rp /root/id_rsa /home/www-data/.ssh
cp -rp /root/known_hosts /home/www-data/.ssh
chmod 0600 /home/www-data/.ssh/id_rsa
chmod u+w /home/www-data/.ssh/known_hosts
chown -R www-data:www-data /home/www-data/.ssh/
sed -ri "s/cryptopro-prod,10.105.196.57/$host_name_cryptopro,$ip_cryptopro/" /home/www-data/.ssh/known_hosts

# first run to test with www-data
#ssh root@cryptopro-prod "/opt/cprocsp/sbin/amd64/cpconfig -license -view"

DIR_STAT="$DIR_HOME/stat"

cd $DIR_STAT/config/

#session
cp session.local.tpl.php session.local.php
#for minikube only
if [ $USE_MINIKUBE -ne 0 ]; then
    sed -i "s%//'class' =>%'class' =>%" session.local.php
fi

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

cp db_pg_cache.local.tpl.php db_pg_cache.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_HOST/" db_pg_cache.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_DB/" db_pg_cache.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_USER'/" db_pg_cache.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_PASSWORD'/" db_pg_cache.local.php

cp db_pg_slave.local.tpl.php db_pg_slave.local.php
#sed -i "s/host=127.0.0.1/host=$POSTGRES_SLAVE_HOST/" db_pg_slave_cache.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_HOST/" db_pg_slave.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_DB/" db_pg_slave.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_USER'/" db_pg_slave.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_PASSWORD'/" db_pg_slave.local.php

cp db_pg_slave_cache.local.tpl.php db_pg_slave_cache.local.php
#sed -i "s/host=127.0.0.1/host=$POSTGRES_SLAVE_HOST/" db_pg_slave_cache.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_HOST/" db_pg_slave.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_DB/" db_pg_slave_cache.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_USER'/" db_pg_slave_cache.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_PASSWORD'/" db_pg_slave_cache.local.php

cp db_pg_calllegs.local.tpl.php db_pg_calllegs.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_LEGS_HOST/" db_pg_calllegs.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_DB/" db_pg_calllegs.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_LEGS_USER'/" db_pg_calllegs.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_LEGS_PASSWORD'/" db_pg_calllegs.local.php

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

cp db_pgsql_nfdump.local.tpl.php db_pgsql_nfdump.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_HOST/" db_pgsql_nfdump.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_NFDUMP_DB/" db_pgsql_nfdump.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_USER'/" db_pgsql_nfdump.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_PASSWORD'/" db_pgsql_nfdump.local.php

cp db_pg_statistic.local.tpl.php db_pg_statistic.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_STATISTICS_HOST/" db_pg_statistic.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_DB/" db_pg_statistic.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_USER'/" db_pg_statistic.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_PASSWORD'/" db_pg_statistic.local.php

cp db_pg_history.local.tpl.php db_pg_history.local.php
sed -i "s/host=127.0.0.1/host=$POSTGRES_HISTORY_HOST/" db_pg_history.local.php
sed -i "s/dbname=nispd/dbname=$POSTGRES_HISTORY_DB/" db_pg_history.local.php
sed -i "s/'username' => 'vagrant'/'username' => '$POSTGRES_HISTORY_USER'/" db_pg_history.local.php
sed -i "s/'password' => 'vagrant'/'password' => '$POSTGRES_HISTORY_PASSWORD'/" db_pg_history.local.php

# Redis
cp cache_redis.local.tpl.php cache_redis.local.php
sed -i "s/'hostname' => 'localhost'/'hostname' => '$REDIS_HOST'/" cache_redis.local.php

# Logger
cp log.local.tpl.php log.local.php
sed -i "s/source = 'developer_stat';/source = '$LOG_SOURCE';/" cache_redis.local.php

# Modules

# Socket
cd $DIR_STAT/modules/socket/config/
cp params.php params.local.php
sed -i "s%'url' => ''%'url' => '$SOCKET_URL'%" params.local.php
sed -i "s%'backend_url' => ''%'backend_url' => '$SOCKET_BACKEND_URL'%" params.local.php
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

# SIM
cd $DIR_STAT/modules/sim/config/
cp params.php params.local.php
sed -i "s/'authorization' => ''/'authorization' => '$SIM_AUTHORIZATION'/" params.local.php
sed -i "s/'transfer_msisdn' => ''/'transfer_msisdn' => '$SIM_TRANSFER_MSISDN'/" params.local.php

# Notifier
cd $DIR_STAT/modules/notifier/
cp config.local.tpl.php config.local.php
sed -i "s%'uri' => ''%'uri' => '$NOTIFIER_URI'%" config.local.php
sed -i "s/'user' => ''/'user' => '$NOTIFIER_USER'/" config.local.php
sed -i "s/'passwd' => ''/'passwd' => '$NOTIFIER_PASSWD'/" config.local.php

# Atol
cd $DIR_STAT/modules/atol/config/
cp params.php params.local.php
sed -i "s%'callbackUrl' => ''%'callbackUrl' => '$ATOL_CALL_BACK_URL'%" params.local.php
#
sed -i "s/'password' => 'password_1'/'password' => '$ATOL_ACCESS_MCN_TELECOM_PASSWORD'/" params.local.php
sed -i "s/'login' => 'login_1'/'login' => '$ATOL_ACCESS_MCN_TELECOM_LOGIN'/" params.local.php
sed -i "s/'groupCode' => 'group_code_1'/'groupCode' => '$ATOL_ACCESS_MCN_TELECOM_GROUP_CODE'/" params.local.php
sed -i "s/'inn' => 'inn_1'/'inn' => '$ATOL_ACCESS_MCN_TELECOM_INN'/" params.local.php
#
sed -i "s/'password' => 'password_11'/'password' => '$ATOL_ACCESS_MCN_TELECOM_RETAIL_PASSWORD'/" params.local.php
sed -i "s/'login' => 'login_11'/'login' => '$ATOL_ACCESS_MCN_TELECOM_RETAIL_LOGIN'/" params.local.php
sed -i "s/'groupCode' => 'group_code_11'/'groupCode' => '$ATOL_ACCESS_MCN_TELECOM_RETAIL_GROUP_CODE'/" params.local.php
sed -i "s/'inn' => 'inn_11'/'inn' => '$ATOL_ACCESS_MCN_TELECOM_RETAIL_INN'/" params.local.php
#
sed -i "s/'password' => 'password_21'/'password' => '$ATOL_ACCESS_MCN_TELECOM_SERVICE_PASSWORD'/" params.local.php
sed -i "s/'login' => 'login_21'/'login' => '$ATOL_ACCESS_MCN_TELECOM_SERVICE_LOGIN'/" params.local.php
sed -i "s/'groupCode' => 'group_code_21'/'groupCode' => '$ATOL_ACCESS_MCN_TELECOM_SERVICE_GROUP_CODE'/" params.local.php
sed -i "s/'inn' => 'inn_21'/'inn' => '$ATOL_ACCESS_MCN_TELECOM_SERVICE_INN'/" params.local.php
#
sed -i "s/'paymentAddress' => ''/'paymentAddress' => '$ATOL_BUY_OR_SELL_PAYMENT_ADDRESS'/" params.local.php
sed -i "s/'sno' => ''/'sno' => '$ATOL_BUY_OR_SELL_SNO'/" params.local.php
sed -i "s/'itemName' => ''/'itemName' => '$ATOL_BUY_OR_SELL_ITEM_NAME'/" params.local.php
sed -i "s/'tax' => ''/'tax' => '$ATOL_BUY_OR_SELL_TAX'/" params.local.php

# mchs
cd $DIR_STAT/modules/mchs/config/
cp params.php params.local.php
sed -i "s/'api_key' => ''/'api_key' => '$MCHS_API_KEY'/" params.local.php

# Async
cd $DIR_STAT/modules/async/config/
cp params.php params.local.php
sed -i "s/'host' => ''/'host' => '$ASYNC_HOST'/" params.local.php
sed -i "s/'user' => ''/'user' => '$ASYNC_USER'/" params.local.php
sed -i "s/'pass' => ''/'pass' => '$ASYNC_PASS'/" params.local.php

# SBIS
cd $DIR_STAT/modules/sbisTenzor/config/
cp params.php params.local.php
sed -i "s/'login' => ''/'login' => '$SBIS_LOGIN'/" params.local.php
sed -i "s/'password' => ''/'password' => '$SBIS_PASSWORD'/" params.local.php

echo "Configs are ready!"

#migrations
cd $DIR_STAT
php yii migrate --interactive=0
php yii migrate/flush-schema

echo "Migrations applied!"

if [ -f /var/run/php-fpm/php-fpm.pid ]; then
    rm /var/run/php-fpm/php-fpm.pid
fi

# by root
#/usr/local/sbin/php-fpm -R

/usr/local/sbin/php-fpm

#/bin/sleep infinity