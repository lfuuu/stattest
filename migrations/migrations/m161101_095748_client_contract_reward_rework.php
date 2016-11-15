<?php

use app\models\ClientContractReward;
use app\models\Transaction;

class m161101_095748_client_contract_reward_rework extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ClientContractReward::tableName();

        $this->addColumn($tableName, 'actual_from', $this->date()->defaultValue('2000-01-01')->notNull());
        $this->addColumn($tableName, 'percentage_of_margin', $this->integer());
        $this->addColumn($tableName, 'user_id', $this->integer());
        $this->addColumn($tableName, 'insert_time', $this->dateTime());

        $rewards = ClientContractReward::find();
        $update = [];

        foreach ($rewards->each() as $reward) {
            $usageType = null;
            switch ($reward->usage_type) {
                case 'voip':
                    $usageType = Transaction::SERVICE_VOIP;
                    break;
                case 'virtpbx':
                    $usageType = Transaction::SERVICE_VIRTPBX;
                    break;
            }

            if (is_null($usageType)) {
                continue;
            }

            $update[$reward->id] = $usageType;
        }

        $this->alterColumn($tableName, 'usage_type', $this->string(60));

        $this->dropIndex('contract_id_usage_type', $tableName);
        $this->createIndex('contract_id-usage_type-actual_from', $tableName, [
            'contract_id', 'usage_type', 'actual_from',
        ], $unique = true);

        foreach ($update as $rewardId => $usageType) {
            $this->update($tableName, ['usage_type' => $usageType], ['id' => $rewardId]);
        }
    }

    public function down()
    {
        $tableName = ClientContractReward::tableName();

        $this->dropIndex('contract_id-usage_type-actual_from', $tableName);
        $this->createIndex('contract_id_usage_type', $tableName, [
            'contract_id', 'usage_type',
        ], $unique = true);

        $this->dropColumn($tableName, 'actual_from');
        $this->dropColumn($tableName, 'percentage_of_margin');
        $this->dropColumn($tableName, 'user_id');
        $this->dropColumn($tableName, 'insert_time');
    }
}