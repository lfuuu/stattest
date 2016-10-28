<?php

use app\models\ClientAccount;

class m161028_103129_clients_type_of_bill extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(ClientAccount::tableName(), 'type_of_bill', $this->boolean()->defaultValue(ClientAccount::TYPE_OF_BILL_DETAILED));
    }

    public function down()
    {
        $this->dropColumn(ClientAccount::tableName(), 'type_of_bill');
    }
}