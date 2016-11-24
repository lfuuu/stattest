<?php

use app\models\ClientContract;

class m161123_095505_client_contract_change_lk_access extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ClientContract::tableName();

        $this->execute('
            ALTER TABLE ' . $tableName . '
                CHANGE COLUMN lk_access is_lk_access TINYINT(1) NOT NULL DEFAULT "0"
        ');
    }

    public function down()
    {
        $tableName = ClientContract::tableName();

        $this->execute('
            ALTER TABLE ' . $tableName . '
	            CHANGE COLUMN is_lk_access lk_access ENUM("full","readonly","noaccess") NULL DEFAULT NULL
        ');
    }
}