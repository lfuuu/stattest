<?php

class m150417_113458_contragent_fields extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `client_person`
            CHANGE COLUMN `date_of_bird` `passport_date_issued`  date NOT NULL AFTER `middle_name`;
        ");

        $this->execute("ALTER TABLE `client_contragent`
            MODIFY COLUMN `opf`  varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `tax_regime`,
                MODIFY COLUMN `okpo`  varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `opf`,
                MODIFY COLUMN `okvd`  varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `okpo`,
                ADD COLUMN `ogrn`  varchar(16) NOT NULL AFTER `okvd`;

        ");

    }

    public function down()
    {
        echo "m150417_113458_contragent_fields cannot be reverted.\n";

        return false;
    }
}
