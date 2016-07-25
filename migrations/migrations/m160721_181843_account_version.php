<?php

use app\models\ClientAccount;

class m160721_181843_account_version extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(ClientAccount::tableName(), 'account_version', $this->integer(1)->unsigned()->defaultValue(ClientAccount::VERSION_BILLER_USAGE));
    }

    public function down()
    {
        $this->dropColumn(ClientAccount::tableName(), 'account_version');
    }
}