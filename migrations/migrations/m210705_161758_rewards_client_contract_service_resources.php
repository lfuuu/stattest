<?php

use app\models\rewards\RewardClientContractResource;
use app\models\rewards\RewardClientContractService;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m210705_161758_rewards_client_contract_service_resources
 */
class m210705_161758_rewards_client_contract_service_resources extends \app\classes\Migration
{

    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(RewardClientContractService::tableName(), [
            'id' => $this->primaryKey(),
            'client_contract_id' => $this->integer(),
            'service_type_id' => $this->integer(),
            'actual_from' => $this->date(),
            'once_only' => $this->integer(),
            'percentage_once_only' => $this->integer(),
            'percentage_of_fee' => $this->integer(),
            'percentage_of_minimal' => $this->integer(),
            'period_type' => "ENUM('month', 'always')",
            'period_month' => $this->integer(),
            'insert_time' => $this->dateTime(),
            'user_id' => $this->integer(),
        ]);

        $this->createTable(RewardClientContractResource::tableName(), [
            'id' => $this->primaryKey(),
            'reward_service_id' => $this->integer(),
            'service_type_id' => $this->integer(),
            'resource_id' => $this->integer(),
            'price_percent' => $this->integer(),
            'percent_margin_fee' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-' . RewardClientContractResource::tableName() . '-service_type_id',
            RewardClientContractResource::tableName(),
            'service_type_id',
            ServiceType::tableName(),
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . RewardClientContractResource::tableName() . '-resource_id',
            RewardClientContractResource::tableName(),
            'resource_id',
            ResourceModel::tableName(),
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . RewardClientContractService::tableName() . '-service_type_id',
            RewardClientContractService::tableName(),
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
        $this->dropTable(RewardClientContractResource::tableName());
        $this->dropTable(RewardClientContractService::tableName());
    }
}
