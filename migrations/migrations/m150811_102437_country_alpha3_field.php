<?php

class m150811_102437_country_alpha3_field extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `country`
                ADD COLUMN `alpha_3` VARCHAR(3) NULL DEFAULT NULL AFTER `code`;
        ");
        $this->executeSqlFile('alpha3.sql');
    }

    public function down()
    {
        echo "m150811_102437_country_alpha3_field cannot be reverted.\n";

        return false;
    }
}