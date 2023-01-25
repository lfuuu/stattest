<?php

/**
 * Class m230125_124451_calltracking_sites
 */
class m230125_124451_calltracking_sites extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(
            \app\modules\uu\models\ServiceType::ID_CALLTRACKING,
            \app\modules\uu\models\ResourceModel::ID_CALLTRACKING_ADDITIONAL_SITES,
            [
                'name' => 'Кол-во дополнительных сайтов',
                'unit' => '¤',
                'min_value' => 0,
                'max_value' => 100,
            ],
            [
                \app\models\Currency::RUB => 29,
                \app\models\Currency::EUR => 0,
                \app\models\Currency::HUF => 0,
                \app\models\Currency::USD => 0,
            ],
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(\app\modules\uu\models\TariffResource::tableName(), [
            'resource_id' => [
                \app\modules\uu\models\ResourceModel::ID_CALLTRACKING_ADDITIONAL_SITES,
            ]
        ]);

        $this->delete(\app\modules\uu\models\AccountTariffResourceLog::tableName(), [
            'resource_id' => [
                \app\modules\uu\models\ResourceModel::ID_CALLTRACKING_ADDITIONAL_SITES,
            ]
        ]);

        $this->delete(\app\modules\uu\models\ResourceModel::tableName(), [
            'id' => [
                \app\modules\uu\models\ResourceModel::ID_CALLTRACKING_ADDITIONAL_SITES,
            ]
        ]);
    }
}
