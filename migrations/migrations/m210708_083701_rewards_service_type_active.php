<?php

use app\models\rewards\RewardsServiceTypeActive;
use app\modules\uu\models\ServiceType;
/**
 * Class m210708_083701_rewards_service_type_active
 */
class m210708_083701_rewards_service_type_active extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(RewardsServiceTypeActive::tableName(), [
            'service_type_id' => $this->primaryKey(),
            'is_active' => $this->boolean(),
        ]);

        $this->addForeignKey(
            'fk-' . RewardsServiceTypeActive::tableName() . '-service_type_id',
            RewardsServiceTypeActive::tableName(),
            'service_type_id',
            ServiceType::tableName(),
            'id',
            'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(RewardsServiceTypeActive::tableName());
    }
}
