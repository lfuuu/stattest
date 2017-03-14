<?php
use app\models\BusinessProcessStatus;

/**
 * Class m170314_133603_rename_bps_gpon
 */
class m170314_133603_rename_bps_gpon extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(BusinessProcessStatus::tableName(), ['name' => 'Shop MCNTele.com'], ['id' => BusinessProcessStatus::PROVIDER_MAINTENANCE_TELESHOP]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(BusinessProcessStatus::tableName(), ['name' => 'GPON'], ['id' => BusinessProcessStatus::PROVIDER_MAINTENANCE_TELESHOP]);
    }
}
