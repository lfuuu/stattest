# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 

APPNAME=stat
TAG=1.316

function dev()
{
	export ENVNAME=dev
	CI_URL="$APPNAME-$ENVNAME.local"
  PGADMIN_IN_DEV="yes"
  export CI_DIR_HOME="/opt/stat_rep"
}

function stage()
{
	export ENVNAME=stage
	CI_URL="$APPNAME-$ENVNAME.local"
	export CI_DIR_HOME="/home/httpd/stat.mcn.local"
}

function prod()
{
	export ENVNAME=prod
	#CI_URL="stat.mcnhost.ru"
	CI_URL="stat.mcn.ru"
	export CI_DIR_HOME="/home/httpd/stat.mcn.ru"
}

