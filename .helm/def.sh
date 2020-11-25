# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 

APPNAME=stat
TAG=1.171

function dev()
{
	ENVNAME=dev
	CI_URL="$APPNAME-$ENVNAME.local"
  PGADMIN_IN_DEV="yes"
  CI_DIR_HOME="/opt/stat_rep"
}

function stage()
{
	ENVNAME=stage
	CI_URL="$APPNAME-$ENVNAME.local"
	CI_DIR_HOME="/home/httpd/stat.mcn.local"
}

function prod()
{
	ENVNAME=prod
	#CI_URL="stat.mcnhost.ru"
	CI_URL="stat2.mcn.ru"
	CI_DIR_HOME="/home/httpd/stat.mcn.ru"
}

