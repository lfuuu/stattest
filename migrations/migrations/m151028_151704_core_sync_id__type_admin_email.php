<?php

class m151028_151704_core_sync_id__type_admin_email extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `core_sync_ids`
            MODIFY COLUMN `type`  enum('account','contragent','admin_email','super_client') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'account' AFTER `id`;
        ");

    }

    public function down()
    {
        echo "m151028_151704_core_sync_id__type_admin_email cannot be reverted.\n";

        return false;
    }
}
