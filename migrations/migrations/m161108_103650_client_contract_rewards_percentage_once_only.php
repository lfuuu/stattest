<?php

use app\models\ClientContractReward;

class m161108_103650_client_contract_rewards_percentage_once_only extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ClientContractReward::tableName();

        $this->addColumn($tableName, 'percentage_once_only', $this->integer());
    }

    public function down()
    {
        $tableName = ClientContractReward::tableName();

        $this->dropColumn($tableName, 'percentage_once_only');
    }
}