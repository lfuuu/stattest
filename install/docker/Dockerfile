FROM centos/systemd

WORKDIR /opt
USER root

RUN mkdir /root/.ssh;\
chmod 700 /root/.ssh
COPY ./id_rsa /root/.ssh/
RUN echo "github.com,192.30.253.113 ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAq2A7hRGmdnm9tUDbO9IDSwBK6TbQa+PXYPCPy6rbTrTtw7PHkccKrpp0yVhp5HdEIcKr6pLlVDBfOLX9QUsyCOV0wzfjIJNlGEYsdlLJizHhbn2mUjvSAHQqZETYP81eFzLQNnPHt4EVVUh7VfDESU84KezmD5QlWpXLmvU31/yMf+Se8xhHTvKSCZIFImWwoG6mbUoWf9nzpIoaSjB+weqqUUmpaaasXVal72J+UX2B+2RPW3RcT0eOzQgqlJL3RKrTJvdsjE3JEAvGq3lGHSZXy28G3skua2SmVi/w4yCE6gbODqnTWlg7+wC604ydGXA8VJiS5ap43JXiUFFAaQ==" >> /root/.ssh/known_hosts
COPY ./.pgpass /root/

RUN yum install -y epel-release;\
yum install -y wget vim;\
yum install -y https://download.postgresql.org/pub/repos/yum/9.4/redhat/rhel-7-x86_64/pgdg-centos94-9.4-3.noarch.rpm;\
wget http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm;\
rpm -ivh mysql-community-release-el7-5.noarch.rpm
RUN yum -y update
RUN yum install -y mysql-server postgresql94 postgresql94-server nginx cronie redis;\
rpm -Uvh https://mirror.webtatic.com/yum/el7/epel-release.rpm;\
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm;\
yum -y update && yum -y upgrade;\
yum install -y php56w php56w-fpm php56w-cli php56w-curl php56w-gd php56w-json php56w-mcrypt php56w-mysqlnd php56w-pgsql php56w-readline php56w-xdebug php56w-xmlrpc php56w-intl php56w-mbstring php56w-bcmath php56w-xmlwriter php56w-process php-redis

COPY nginx.conf /etc/nginx/nginx.conf
COPY nginx.default.conf /etc/nginx/conf.d/default.conf
COPY php.ini /etc/php5/fpm/php.ini
COPY php.ini /etc/php5/cli/php.ini
COPY php-fpm.www.conf /etc/php5/fpm/pool.d/www.conf
COPY restore_db.sh /opt
COPY start_db.sh /opt
COPY test_settings.sh /opt

RUN yum install -y git;\
curl -sS https://getcomposer.org/installer | php;\
mv composer.phar /usr/local/bin/composer

EXPOSE 80 8080 5432 3306

CMD ["/usr/sbin/init"]
