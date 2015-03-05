<?php

class m150304_181347_account_is_active extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `clients`
            ADD COLUMN `is_closed`  tinyint NOT NULL DEFAULT 0 AFTER `is_blocked`;
        ");

        $this->execute("
            DROP TABLE `usage_8800`;

            DROP TABLE `tarifs_8800`;

            ALTER TABLE `emails`
            MODIFY COLUMN `status`  enum('connecting','working','archived') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'working' AFTER `smtp_auth`;

            ALTER TABLE `usage_extra`
            MODIFY COLUMN `status`  enum('connecting','working','archived') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `amount`;

            ALTER TABLE `usage_ip_ports`
            MODIFY COLUMN `status`  enum('connecting','working','archived') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `date_last_writeoff`;

            ALTER TABLE `usage_sms`
            MODIFY COLUMN `status`  enum('connecting','working','archived') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `actual_to`;

            ALTER TABLE `usage_virtpbx`
            MODIFY COLUMN `status`  enum('connecting','working','archived') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `amount`;

            ALTER TABLE `usage_voip`
            MODIFY COLUMN `status`  enum('connecting','working','archived') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `date_last_writeoff`;

            ALTER TABLE `usage_welltime`
            MODIFY COLUMN `status`  enum('connecting','working','archived') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `amount`;
        ");

        $this->execute("
            update emails set actual_to = actual_from where actual_from > actual_to;
            update emails set status='archived' where actual_to < now();
            update emails set actual_from='2029-01-01' where actual_from > '2020-01-01' and actual_from != '2029-01-01';
            update emails set actual_to='2029-01-01' where actual_to > '2020-01-01' and actual_to != '2029-01-01';
            update emails set status='connecting' where actual_from = '2029-01-01';
            update emails set status='working' where actual_to >= now() and actual_from != '2029-01-01' and status != 'working';

            update usage_extra set actual_to = actual_from where actual_from > actual_to;
            update usage_extra set status='archived' where actual_to < now();
            update usage_extra set actual_from='2029-01-01' where actual_from > '2020-01-01' and actual_from != '2029-01-01';
            update usage_extra set actual_to='2029-01-01' where actual_to > '2020-01-01' and actual_to != '2029-01-01';
            update usage_extra set status='connecting' where actual_from = '2029-01-01';
            update usage_extra set status='working' where actual_to >= now() and actual_from != '2029-01-01' and status != 'working';

            update usage_ip_ports set actual_to = actual_from where actual_from > actual_to;
            update usage_ip_ports set status='archived' where actual_to < now();
            update usage_ip_ports set actual_from='2029-01-01' where actual_from > '2020-01-01' and actual_from != '2029-01-01';
            update usage_ip_ports set actual_to='2029-01-01' where actual_to > '2020-01-01' and actual_to != '2029-01-01';
            update usage_ip_ports set status='connecting' where actual_from = '2029-01-01';
            update usage_ip_ports set status='working' where actual_to >= now() and actual_from != '2029-01-01' and status != 'working';

            update usage_sms set actual_to = actual_from where actual_from > actual_to;
            update usage_sms set status='archived' where actual_to < now();
            update usage_sms set actual_from='2029-01-01' where actual_from > '2020-01-01' and actual_from != '2029-01-01';
            update usage_sms set actual_to='2029-01-01' where actual_to > '2020-01-01' and actual_to != '2029-01-01';
            update usage_sms set status='connecting' where actual_from = '2029-01-01';
            update usage_sms set status='working' where actual_to >= now() and actual_from != '2029-01-01' and status != 'working';

            update usage_virtpbx set actual_to = actual_from where actual_from > actual_to;
            update usage_virtpbx set status='archived' where actual_to < now();
            update usage_virtpbx set actual_from='2029-01-01' where actual_from > '2020-01-01' and actual_from != '2029-01-01';
            update usage_virtpbx set actual_to='2029-01-01' where actual_to > '2020-01-01' and actual_to != '2029-01-01';
            update usage_virtpbx set status='connecting' where actual_from = '2029-01-01';
            update usage_virtpbx set status='working' where actual_to >= now() and actual_from != '2029-01-01' and status != 'working';

            update usage_voip set actual_to = actual_from where actual_from > actual_to;
            update usage_voip set status='archived' where actual_to < now();
            update usage_voip set actual_from='2029-01-01' where actual_from > '2020-01-01' and actual_from != '2029-01-01';
            update usage_voip set actual_to='2029-01-01' where actual_to > '2020-01-01' and actual_to != '2029-01-01';
            update usage_voip set status='connecting' where actual_from = '2029-01-01';
            update usage_voip set status='working' where actual_to >= now() and actual_from != '2029-01-01' and status != 'working';

            update usage_welltime set actual_to = actual_from where actual_from > actual_to;
            update usage_welltime set status='archived' where actual_to < now();
            update usage_welltime set actual_from='2029-01-01' where actual_from > '2020-01-01' and actual_from != '2029-01-01';
            update usage_welltime set actual_to='2029-01-01' where actual_to > '2020-01-01' and actual_to != '2029-01-01';
            update usage_welltime set status='connecting' where actual_from = '2029-01-01';
            update usage_welltime set status='working' where actual_to >= now() and actual_from != '2029-01-01' and status != 'working';

            update emails u, clients c set u.actual_from='0000-00-00', u.actual_to='0000-00-00', u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='connecting';
            update emails u, clients c set u.actual_to=now(), u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='working';

            update usage_extra u, clients c set u.actual_from='0000-00-00', u.actual_to='0000-00-00', u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='connecting';
            update usage_extra u, clients c set u.actual_to=now(), u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='working';

            update usage_ip_ports u, clients c set u.actual_from='0000-00-00', u.actual_to='0000-00-00', u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='connecting';
            update usage_ip_ports u, clients c set u.actual_to=now(), u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='working';

            update usage_sms u, clients c set u.actual_from='0000-00-00', u.actual_to='0000-00-00', u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='connecting';
            update usage_sms u, clients c set u.actual_to=now(), u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='working';

            update usage_virtpbx u, clients c set u.actual_from='0000-00-00', u.actual_to='0000-00-00', u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='connecting';
            update usage_virtpbx u, clients c set u.actual_to=now(), u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='working';

            update usage_voip u, clients c set u.actual_from='0000-00-00', u.actual_to='0000-00-00', u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='connecting';
            update usage_voip u, clients c set u.actual_to=now(), u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='working';

            update usage_welltime u, clients c set u.actual_from='0000-00-00', u.actual_to='0000-00-00', u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='connecting';
            update usage_welltime u, clients c set u.actual_to=now(), u.status='archived' where c.client=u.client and u.status != 'archived' and c.is_active=0 and u.status='working';

        ");
    }

    public function down()
    {
        echo "m150304_181347_account_is_active cannot be reverted.\n";

        return false;
    }
}