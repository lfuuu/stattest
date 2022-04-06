# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 

APPNAME=stat
TAG=1.352

function dev()
{
	export ENVNAME=dev
	CI_URL="stat.mcn.loc"
  PGADMIN_IN_DEV="yes"
  export CI_DIR_HOME="/home/httpd/stat.mcn.ru"
  export COUNTRY="RUS"

  export IS_WITH_PHPMYADMIN=1
  export IS_WITH_PGADMIN=1
  export IS_WITH_CRYPTOPRO=0
  export IS_WITH_COMET=0
  export IS_WITH_GRAPHQL=0
  export IS_WITH_NNPPORTED=0
}

function dev_eu()
{
	export ENVNAME=dev
	CI_URL="stat.mcntelecom.loc"
  PGADMIN_IN_DEV="yes"
  export CI_DIR_HOME="/home/httpd/stat.mcn.ru"
  export COUNTRY="EU"

  export IS_WITH_PHPMYADMIN=1
  export IS_WITH_PGADMIN=1
  export IS_WITH_CRYPTOPRO=0
  export IS_WITH_COMET=0
  export IS_WITH_GRAPHQL=0
  export IS_WITH_NNPPORTED=0
}

function prod()
{
	export ENVNAME=prod
	CI_URL="stat.mcn.ru"
	export CI_DIR_HOME="/home/httpd/stat.mcn.ru"
	export COUNTRY="RUS"

  export IS_WITH_PHPMYADMIN=0
  export IS_WITH_PGADMIN=0
  export IS_WITH_CRYPTOPRO=1
  export IS_WITH_COMET=1
  export IS_WITH_GRAPHQL=1
  export IS_WITH_NNPPORTED=1
}

function prod_eu()
{
	export ENVNAME=prod
	CI_URL="stat.mcntele.com"
	export CI_DIR_HOME="/home/httpd/stat.mcn.ru"
	export COUNTRY="EU"

  export IS_WITH_PHPMYADMIN=0
  export IS_WITH_PGADMIN=0
  export IS_WITH_CRYPTOPRO=0
  export IS_WITH_COMET=1
  export IS_WITH_GRAPHQL=0
  export IS_WITH_NNPPORTED=0
}

