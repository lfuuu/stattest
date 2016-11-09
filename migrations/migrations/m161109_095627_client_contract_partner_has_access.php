<?php

use app\models\ClientContract;

class m161109_095627_client_contract_partner_has_access extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ClientContract::tableName();

        $this->addColumn($tableName, 'partner_login_allow', $this->boolean()->defaultValue(false));
    }

    public function down()
    {
        $tableName = ClientContract::tableName();

        $this->dropColumn($tableName, 'partner_login_allow');
    }
}