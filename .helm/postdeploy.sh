#!/bin/bash

THIS=`readlink -f "${BASH_SOURCE[0]}"`
DIR=`dirname "${THIS}"`
cd $DIR

rm -rf .werf/tmp/id_rsa
rm -rf .werf/tmp/.pgpass

source ./def.sh

env=$1
$env

if [ "$ENVNAME" = "dev-section-off" ]; then
    NAMESPACE="$APPNAME-$ENVNAME"
    PODNAME=$(kubectl get pods -n $NAMESPACE | grep $APPNAME | grep backend | awk '{print $1}')

#    echo ">>> Копируем ключ ssh"
#    kubectl -n $NAMESPACE wait --for=condition=ready --timeout=120s pods $PODNAME
#    kubectl -n $NAMESPACE exec -it -c php-fpm $PODNAME -- sh /root/prepare-scripts/copy_id_rsa.sh `base64 -w 0 ~/.ssh/id_rsa`
#    kubectl -n $NAMESPACE exec -it -c php-fpm $PODNAME -- sh /root/prepare-scripts/init-dev-env.sh
#    echo "<<< Копируем ключ ssh"

#    set -e
    DB_EXISTS=$(kubectl exec -ti -n $NAMESPACE mysqldb-0 -- mysql mysql -e "show databases like 'nispd';" | wc -l)
    if [ "$DB_EXISTS" -eq "0" ]; then
      echo "mysql DB: creating"
      kubectl exec -ti -n $NAMESPACE mysqldb-0 -- bash /root/00-init-db.sh
    fi

    kubectl exec -ti -n $NAMESPACE mysqldb-0 -- ls -l /tmp/nispd.sql &> /dev/null
    FILE_NISPDSQL_EXISTS=$?

    if [ "$FILE_NISPDSQL_EXISTS" -ne "0" ]; then
      echo "mysql DB: get dump"
      kubectl exec -ti -c php-fpm -n $NAMESPACE $PODNAME -- \
        cat /home/httpd/stat.mcn.ru/stat/migrations/migrations/data/m100000_000001_init/nispd.sql > /tmp/nispd.sql
      kubectl cp -n $NAMESPACE /tmp/nispd.sql mysqldb-0:/tmp/
      rm -rf /tmp/nispd.sql
    fi

    USER_TABLE_EXISTS=$(kubectl exec -ti -n $NAMESPACE mysqldb-0 -- mysql -e "show tables like 'z_sync_postgres';" | wc -l)
    if [ "$USER_TABLE_EXISTS" -ne '5' ]; then
      echo 'mysql DB: apply dump'
      kubectl exec -ti -n $NAMESPACE mysqldb-0 -- mysql -e '\. /tmp/nispd.sql'
      kubectl exec -ti -n $NAMESPACE mysqldb-0 -- bash /root/01-set-000001-migration.sh

      echo 'mysql DB: apply migrations'
      kubectl exec -ti -n $NAMESPACE -c php-fpm $PODNAME -- /home/httpd/stat.mcn.ru/stat/yii migrate --interactive=0
    fi
fi

if [ "$ENVNAME" = "dev" ]; then
  NAMESPACE="$APPNAME-$ENVNAME"
  PODNAME=$(kubectl get pods -n $NAMESPACE | grep $APPNAME | grep backend | awk '{print $1}')

  echo 'mysql DB: apply migrations'
  kubectl exec -ti -n $NAMESPACE -c php-fpm $PODNAME -- /home/httpd/stat.mcn.ru/stat/yii migrate --interactive=0


  ### phpMyAdmin
  PHPADMIN_URL=$(kubectl get ingress | grep phpmyadmin | awk '{print $3}' | sed "s/[[:space:]]//")
  minikubeIp=$(minikube ip)

  if [ "$PHPADMIN_URL" != "" ]; then
    sudo sed -i -e "/^.*${PHPADMIN_URL}/d" /etc/hosts
    echo "${minikubeIp} ${PHPADMIN_URL}" | sudo tee -a /etc/hosts
    echo "PhpMyAdmin доступен по адресу: http://$PHPADMIN_URL"
  else
    echo "PhpMyAdmin не доступен"
  fi

  ### pgadmin
  PGADMIN_URL=$(kubectl get ingress | grep pgadmin | awk '{print $3}' | sed "s/[[:space:]]//")
  if [ "$PGADMIN_URL" != "" ]; then
#    sudo sed -i -e "/^.*${PGADMIN_URL}/d" /etc/hosts
#    echo "${minikubeIp} ${PGADMIN_URL}" | sudo tee -a /etc/hosts
    echo "PgAdmin доступен по адресу: http://$PGADMIN_URL/pgadmin4"
  else
    echo "PgAdmin не доступен"
  fi


  mysqlNodePort=$(kubectl get service -n $NAMESPACE mysqldb | grep mysqldb | awk '{print $5}' | sed -r 's/^(.*):(.*)\/.*/\2/g')
  mysqlPassword=$(kubectl exec -t -n stat-dev mysql-dev-0 -- env | grep MYSQLDB_PASSWORD | sed 's/.*=//g')
  mysqlUser=$(kubectl exec -t -n stat-dev mysql-dev-0 -- env | grep MYSQLDB_USER | sed 's/.*=//g')
  mysqlDb=$(kubectl exec -t -n stat-dev mysql-dev-0 -- env | grep MYSQLDB_DB | sed 's/.*=//g')

  echo "MySQL uri: mysql://${mysqlUser}:${mysqlPassword}@${minikubeIp}:${mysqlNodePort}/${mysqlDb}"
  echo "Stat login: admin/111"

  PGDB="postgres-history-dev-0"
  if [ -f $HOME/.pgpass ]; then
    foundPgPass=$(kubectl exec -ti -n $NAMESPACE $PGDB -- bash -c "[ -f /root/.pgpass ] || echo 'Not found'")
    if [[ $foundPgPass =~ "Not found" ]]; then
      kubectl cp -n $NAMESPACE $HOME/.pgpass $PGDB:/root/.pgpass
      kubectl exec -ti -n $NAMESPACE $PGDB -- bash -c "chmod 0600 /root/.pgpass"
    fi
  else
    echo '~/.pgpass not found'
  fi

  PODNAME=$(kubectl get pods -n $NAMESPACE | grep node-balance | awk '{print $1}')

  echo 'Check init node balance'
  kubectl exec -ti -n $NAMESPACE $PODNAME -- /workspace/install-app.sh


fi

