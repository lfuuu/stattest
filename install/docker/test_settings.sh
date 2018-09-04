#!/bin/bash
mkdir /var/log/nispd
echo "* * * * * cd /opt/stat/stat/crons/events/; flock -w 3 /tmp/handler1 php ./handler.php with_account_tariff >> /var/log/nispd/handler_with_at.log 2>&1" >> /etc/crontab
echo "* * * * * cd /opt/stat/stat/crons/events/; flock -w 3 /tmp/handler2 php ./handler.php without_account_tariff >> /var/log/nispd/handler_without_at.log 2>&" >> /etc/crontab
crontab -u root /etc/crontab
systemctl restart crond
