<?php

class m150305_181347_account_is_active_rollback extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            update emails set status='working' where status='archived';
            update usage_extra set status='working' where status='archived';
            update usage_ip_ports set status='working' where status='archived';
            update usage_sms set status='working' where status='archived';
            update usage_virtpbx set status='working' where status='archived';
            update usage_voip set status='working' where status='archived';
            update usage_welltime set status='working' where status='archived';
        ");

        $this->execute("
            ALTER TABLE `emails`
            MODIFY COLUMN `status`  enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `smtp_auth`;

            ALTER TABLE `usage_extra`
            MODIFY COLUMN `status`  enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `amount`;

            ALTER TABLE `usage_ip_ports`
            MODIFY COLUMN `status`  enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `date_last_writeoff`;

            ALTER TABLE `usage_sms`
            MODIFY COLUMN `status`  enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `actual_to`;

            ALTER TABLE `usage_virtpbx`
            MODIFY COLUMN `status`  enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `amount`;

            ALTER TABLE `usage_voip`
            MODIFY COLUMN `status`  enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `date_last_writeoff`;

            ALTER TABLE `usage_welltime`
            MODIFY COLUMN `status`  enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working' AFTER `amount`;
        ");
    }

    public function down()
    {
        echo "m150305_181347_account_is_active_rollback cannot be reverted.\n";

        return false;
    }
}