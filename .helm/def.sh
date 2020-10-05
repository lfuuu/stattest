# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 

APPNAME=stat
TAG=1.136

function dev()
{
	ENVNAME=dev
	CI_URL="$APPNAME-$ENVNAME.local"
  PGADMIN_IN_DEV="yes"
}

function stage()
{
	ENVNAME=stage
	CI_URL="$APPNAME-$ENVNAME.local"
}

function prod()
{
	ENVNAME=prod
	#CI_URL="stat.mcnhost.ru"
	CI_URL="stat2.mcn.ru"
}

