<?php

class m150416_123743_contragent_address_post extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contragent`
                CHANGE COLUMN `address` `address_jur`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `name_full`,
                ADD    COLUMN `address_post`           varchar(255) NOT NULL AFTER `address_jur`,
                CHANGE COLUMN `inn_eu` `inn_euro`      varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `inn`


        ");

    }

    public function down()
    {
        echo "m150416_123743_contragent_address_post cannot be reverted.\n";

        return false;
    }
}
