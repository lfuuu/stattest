# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 

APPNAME=stat
TAG=1.347

function dev()
{
	export ENVNAME=dev
	CI_URL="stat.mcn.loc"
  PGADMIN_IN_DEV="yes"
  export CI_DIR_HOME="/home/httpd/stat.mcn.ru"
  export COUNTRY="RUS"

  export is_with_phpmyadmin=1
  export is_with_cryptopro=1
  export is_with_comet=1
  export is_with_graphql=1
  export is_with_nnpported=0
}

function dev_hun()
{
	export ENVNAME=dev
	CI_URL="stat.mcntelecom.loc"
  PGADMIN_IN_DEV="yes"
  export CI_DIR_HOME="/home/httpd/stat.mcn.ru"
  export COUNTRY="HUN"

  export is_with_phpmyadmin=1
  export is_with_cryptopro=0
  export is_with_comet=0
  export is_with_graphql=0
  export is_with_nnpported=0
}

function prod()
{
	export ENVNAME=prod
	CI_URL="stat.mcn.ru"
	export CI_DIR_HOME="/home/httpd/stat.mcn.ru"
	export COUNTRY="RUS"

  export is_with_phpmyadmin=0
  export is_with_cryptopro=1
  export is_with_comet=1
  export is_with_graphql=1
  export is_with_nnpported=1
}

function prod_hun()
{
	export ENVNAME=prod
	CI_URL="stat.mcntele.com"
	export CI_DIR_HOME="/home/httpd/stat.mcn.ru"
	export COUNTRY="HUN"

  export is_with_phpmyadmin=0
  export is_with_cryptopro=0
  export is_with_comet=1
  export is_with_graphql=0
  export is_with_nnpported=0
}

