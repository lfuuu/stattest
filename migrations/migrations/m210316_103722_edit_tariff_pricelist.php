<?php

use app\classes\Migration;
use app\models\UserRight;

/**
 * Class m210316_103722_edit_tariff_pricelist
 */
class m210316_103722_edit_tariff_pricelist extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(UserRight::tableName(), [
            'values' => 'read,edit,priceEdit',
            'values_desc' => 'чтение,изменение,редактирование прайс-листов',
        ], [
                'resource' => 'tarifs',
            ]
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(UserRight::tableName(), [
            'values' => 'read,edit',
            'values_desc' => 'чтение,изменение',
        ], [
                'resource' => 'tarifs',
            ]
        );

    }
}
