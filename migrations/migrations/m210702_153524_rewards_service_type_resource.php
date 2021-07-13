<?php

use app\models\rewards\RewardsServiceTypeResource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\ResourceModel;

/**
 * Class m210702_153524_rewards_service_type_resource
 */
class m210702_153524_rewards_service_type_resource extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(RewardsServiceTypeResource::tableName(), [
            'id' => $this->primaryKey(),
            'service_type_id' => $this->integer(),
            'resource_id' => $this->integer(),
            'is_active' => $this->boolean(),
        ]);

        $this->addForeignKey(
            'fk-' . RewardsServiceTypeResource::tableName() . '-service_type_id',
            RewardsServiceTypeResource::tableName(),
            'service_type_id',
            ServiceType::tableName(),
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . RewardsServiceTypeResource::tableName() . '-resource_id',
            RewardsServiceTypeResource::tableName(),
            'resource_id',
            ResourceModel::tableName(),
            'id',
            'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(RewardsServiceTypeResource::tableName());
    }
}
