<?php

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\NnpLog;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;

/**
 * Class m171215_154342_add_uu_nnp
 */
class m171215_154342_add_uu_nnp extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_NNP,
            'name' => 'ННП'
        ]);

        $this->insert(Resource::tableName(), [
            'id' => Resource::ID_NNP_NUMBERS,
            'name' => 'Номера',
            'unit' => 'Unit',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_NNP,
        ]);

        $this->createTable(NnpLog::tableName(), [
            'id' => $this->primaryKey(),
            'account_tariff_id' => $this->integer(),
            'insert_time' => $this->dateTime(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey('account_tariff_id', NnpLog::tableName(), 'account_tariff_id', AccountTariff::tableName(), 'id', 'RESTRICT');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(NnpLog::tableName());
        $this->delete(Resource::tableName(), ['id' => Resource::ID_NNP_NUMBERS]);
        $this->delete(ServiceType::tableName(), ['id' => ServiceType::ID_NNP]);
    }
}
