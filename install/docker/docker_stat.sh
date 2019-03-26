#!/bin/bash

set -e
IMAGE="stat"
CONTAINER="stat_app"
DIR=$(dirname "$(readlink -f "$0")")
cd $DIR

cp ~/.ssh/id_rsa .
cp ~/.pgpass .
cp ../nginx.conf .
cp ../nginx.default.conf .
cp ../php.ini .
cp ../php-fpm.www.conf .
set +e
build()
{
	set -e
	if [ $(docker images| grep -w $IMAGE | wc -l) -ne 0 ]; then
		echo "please make rm before build"
		exit 1
	fi

	if [ ! -f ~/.pgpass ]; then
    		echo "no .pgpass"
		exit 1
	fi

	perm=$(stat --format '%a' ~/.pgpass)

        if [ ! $perm -eq 600 ]; then
                echo "wrong permitions for .pgpass, use chmod 600 .pgpass"
                exit 1
        fi


	if [ $(cat ~/.pgpass | grep "85.94.32.228:\*:\*:pgsqltest" | wc -l) -ne 1 ]; then
                echo "no pgsqltest creadential for iberus in .pgpass"
		exit 1
        fi

	if [ -d ~/stat_dev_docker/stat ]; then
		echo "please rm -rf ~/stat_dev_docker/stat before build"
		exit 1
	fi

	docker build --force-rm --no-cache -t $IMAGE .
	docker run --privileged -d -v ~/stat_dev_docker:/opt/stat_rep --name $CONTAINER $IMAGE
	docker exec -u root -w /opt/stat_rep -it $CONTAINER git clone git@github.com:welltime/stat.git
	docker exec -u root -w /opt/stat_rep -it $CONTAINER mkdir -p /opt/stat_rep/store
	docker exec -u root -w /opt/stat_rep -it $CONTAINER chmod 777 /opt/stat_rep/store/
	docker exec -u root -w /opt/stat_rep -it $CONTAINER mkdir -p /opt/stat_rep/store/contracts
	docker exec -u root -w /opt/stat_rep -it $CONTAINER chmod 777 /opt/stat_rep/store/contracts/
	docker exec -u root -w /opt/stat_rep -it $CONTAINER mkdir -p /opt/stat_rep/store/files
	docker exec -u root -w /opt/stat_rep -it $CONTAINER mkdir -p /opt/stat_rep/store/files/invoice_content
	docker exec -u root -w /opt/stat_rep -it $CONTAINER mkdir -p /opt/stat_rep/store/files/payment_templates
	docker exec -u root -w /opt/stat_rep -it $CONTAINER chmod -R 777 /opt/stat_rep/store/files/
	docker exec -u root -w /opt/stat_rep/stat -it $CONTAINER /usr/local/bin/composer install
	docker exec -u root -w /opt/stat_rep/stat -it $CONTAINER /usr/local/bin/composer global require "fxp/composer-asset-plugin"
	docker exec -u root -w /opt/stat_rep/stat -it $CONTAINER /usr/local/bin/composer global require "codeception/codeception=2.0.*"
	docker exec -u root -w /opt/stat_rep/stat -it $CONTAINER /usr/local/bin/composer global require "codeception/specify=*"
	docker exec -u root -w /opt/stat_rep/stat -it $CONTAINER /usr/local/bin/composer global require "codeception/verify=*"
	docker exec -u root -w /opt/stat_rep/stat -it $CONTAINER /usr/local/bin/composer install
	docker exec -u root -w /bin -it $CONTAINER ln -s /root/.composer/vendor/bin/codecept codecept
	docker exec -u root -w /opt/stat_rep/stat -it $CONTAINER chmod 777 web/assets
	docker exec -u root -w /opt/stat_rep/stat -it $CONTAINER cp stat/local.conf.php.tpl stat/local.conf.php
	docker exec -u root -w /opt/stat_rep/stat -it $CONTAINER mkdir stat/design_c
	docker exec -u root -w /opt/stat_rep/stat -it $CONTAINER chmod 777 stat/design_c
	docker exec -u root -w /opt/stat_rep/stat/config -it $CONTAINER  cp db_stat.php db_stat.local.php
	docker exec -u root -w /opt/stat_rep/stat/config -it $CONTAINER  sed -i "s/'username' => 'vagrant'/'username' => 'stat'/" db_stat.local.php
	docker exec -u root -w /opt/stat_rep/stat/config -it $CONTAINER  sed -i "s/'password' => 'vagrant'/'password' => 'stat'/" db_stat.local.php
	docker exec -u root -w /opt/stat_rep/stat/tests/codeception/config/ -it $CONTAINER  cp config.tpl.php config.php
	docker exec -u root -w /opt/stat_rep/stat/tests/codeception/config/ -it $CONTAINER  sed -i "s/'username' => 'root'/'username' => 'stat'/" config.php
	docker exec -u root -w /opt/stat_rep/stat/tests/codeception/config/ -it $CONTAINER  sed -i "s/'password' => '123'/'password' => 'stat'/" config.php
	docker exec -u root -w /etc -it $CONTAINER  sed -i "s/sql_mode=NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES/sql_mode=/" my.cnf
	docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  cp  db_pgsql.local.tpl.php db_pgsql.local.php
        docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  sed -i "s/'username' => 'vagrant'/'username' => 'stat'/" db_pgsql.local.php
        docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  sed -i "s/'password' => 'vagrant'/'password' => 'stat'/" db_pgsql.local.php
        docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  cp  db_pg_slave.local.tpl.php db_pg_slave.local.php
	docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  sed -i "s/'username' => 'vagrant'/'username' => 'stat'/" db_pg_slave.local.php
        docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  sed -i "s/'password' => 'vagrant'/'password' => 'stat'/" db_pg_slave.local.php
        docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  cp  db_pg_nnp.local.tpl.php db_pg_nnp.local.php
	docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  sed -i "s/'username' => 'vagrant'/'username' => 'stat'/" db_pg_nnp.local.php
        docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  sed -i "s/'password' => 'vagrant'/'password' => 'stat'/" db_pg_nnp.local.php
	docker exec -u root -w /opt/stat_rep/stat/stat -it $CONTAINER  sed -i "s/\"AUTOCREATE_VPBX\" => 0/\"AUTOCREATE_VPBX\" => 1/" local.conf.php
	docker exec -u root -w /opt/stat_rep/stat/tests/codeception -it $CONTAINER  cp web.suite.yml.tpl web.suite.yml
        docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  cp  params.local.tpl.php params.local.php
	docker exec -u root -w /opt/stat_rep/stat/config/ -it $CONTAINER  sed -i "s/'API_SECURE_KEY' => ''/'API_SECURE_KEY' => '|H;\\\\\\\9P\$.N4\/Y\$V\\\\\\\9A\$#l'/" params.local.php
	docker exec -u root --privileged -it $CONTAINER ./test_settings.sh
	docker exec -u root --privileged -d $CONTAINER systemctl enable nginx
	docker exec -u root --privileged -d $CONTAINER systemctl start nginx
	docker exec -u root --privileged -d $CONTAINER systemctl start php-fpm
	docker exec -u root --privileged  $CONTAINER mkdir data
	docker exec -u root --privileged  $CONTAINER chmod 700 data
	docker exec -u root --privileged  $CONTAINER chown postgres:postgres data
	docker exec -u postgres --privileged  $CONTAINER /usr/pgsql-9.4/bin/pg_ctl -D data init
	docker exec -u postgres --privileged -d $CONTAINER /usr/pgsql-9.4/bin/pg_ctl -D data start
	docker exec -u postgres --privileged -d $CONTAINER systemctl start mysql
	docker exec -u root --privileged  $CONTAINER ./start_db.sh
	docker exec -u root --privileged  $CONTAINER ./restore_db.sh
	docker exec -u root -w /opt/stat_rep/stat $CONTAINER ./yii migrate --interactive=0
	echo "build finish"
	echo "stat is working at address - $(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $CONTAINER):80"
	echo "login - admin/111"
	echo "stat local folder is ~/stat_dev_docker/stat"
}

rm()
{
	if [ -d ~/stat_dev_docker/stat ]; then
		echo "you have local stat (~/stat_dev_docker/stat) that will be removed"
		echo "continue? yes/no"
		read x
		if [ "$x" = "yes" ]; then
			sudo /bin/rm -rf ~/stat_dev_docker/stat
		else
		        exit 0
		fi
	fi
	
	volume=$(docker inspect -f '{{range .Mounts}}{{.Name}}{{end}}' $CONTAINER)
	docker rm -f $CONTAINER
	docker rmi -f $IMAGE
	docker volume rm $volume
}


bash()
{
	docker exec -u root --privileged -it $CONTAINER bash
}

info()
{
	echo "CONTAINER INFO"
	echo ""
        echo "name - $(docker inspect -f '{{.Name}}' $CONTAINER)"
       	echo "id - $(docker inspect -f '{{.Id}}' $CONTAINER)"
	echo "ip - $(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $CONTAINER)"
	echo "mounts - $(docker inspect -f '{{range .Mounts}}{{.Name}}{{end}}' $CONTAINER), destination -  $(docker inspect -f '{{range .Mounts}}{{.Destination}}{{end}}' $CONTAINER) , source - $(docker inspect -f '{{range .Mounts}}{{.Source}}{{end}}' $CONTAINER)"
	echo "volumes - $(docker inspect -f '{{.Config.Volumes}}' $CONTAINER)"
	echo "working dir - $(docker inspect -f '{{.Config.WorkingDir}}' $CONTAINER)"
	echo "env - $(docker inspect -f '{{.Config.Env}}' $CONTAINER) "
	echo "user - $(docker inspect -f '{{.Config.User}}' $CONTAINER)"
	echo "hostname - $(docker inspect -f '{{.Config.Hostname}}' $CONTAINER)"
	echo "exposed ports - $(docker inspect -f '{{ .Config.ExposedPorts}}' $CONTAINER)"
	echo "ports - $(docker inspect -f '{{ .NetworkSettings.Ports }}' $CONTAINER)"
	echo "cmd - $(docker inspect -f '{{.Config.Cmd}}' $CONTAINER) "
	echo "image: name -  $(docker inspect -f '{{.Config.Image}}' $CONTAINER), hash - $(docker inspect -f '{{.Image}}' $CONTAINER)"
	echo "status - $(docker inspect -f '{{.State.Status}}' $CONTAINER) "

}

restart()
{
	set +e
	docker restart $CONTAINER
        docker exec -u root --privileged  $CONTAINER systemctl restart nginx
        docker exec -u root --privileged  $CONTAINER systemctl restart php-fpm
        docker exec -u root --privileged  $CONTAINER systemctl restart mysql
	docker exec -u root --privileged -d $CONTAINER su -c "/usr/pgsql-9.4/bin/pg_ctl -D data restart" postgres >> /dev/null
        docker exec -u root --privileged -d $CONTAINER ./restore_db.sh
}

case "$1" in
		build)      build ;;
	        rm)         rm ;;
		rebuild)    rm
			    build 
			    ;;
		restart)    restart ;;
		bash)       bash ;;
		info)       info ;;
		*)          echo "usage {build | rm | rebuild | restart | bash | info}" ;;
esac

exit 0
