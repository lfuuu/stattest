<?php

class m150224_181512_client_account_manager extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `client_super` ADD COLUMN `account_manager`  varchar(30) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE `log_client` ADD COLUMN `super_id`  int(11) NOT NULL DEFAULT 0 AFTER `client_id`");
        $this->execute("UPDATE client_super cs, clients c SET cs.account_manager = c.account_manager WHERE client NOT REGEXP '\/' AND super_id = cs.id");
    }

    public function down()
    {
        echo "m150224_181512_client_account_manager cannot be reverted.\n";

        return false;
    }
}
