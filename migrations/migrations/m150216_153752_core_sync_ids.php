<?php

class m150216_153752_core_sync_ids extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("CREATE TABLE `core_sync_ids` (
            `id`  int(4) NOT NULL ,
            `type`  enum('account','contragent','super_client') NOT NULL DEFAULT 'account' ,
            `external_id`  varchar(32) NOT NULL ,
            INDEX `type_id` (`id`, `type`) 
        )");

        $this->execute("ALTER TABLE `clients`
            ADD INDEX `super_id` (`super_id`) ,
            ADD INDEX `contragent_id` (`contragent_id`) 
        ");
    }

    public function down()
    {
        echo "m150216_153752_core_sync_ids cannot be reverted.\n";

        return false;
    }
}
