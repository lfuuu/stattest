<?php

use app\models\EquipmentUser;

/**
 * Class m200326_143655_equip_user_idx
 */
class m200326_143655_equip_user_idx extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createIndex('equipment_user-client_account_id', EquipmentUser::tableName(), 'client_account_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropIndex('equipment_user-client_account_id', EquipmentUser::tableName());
    }
}
