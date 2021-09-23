<?php

namespace app\models\rewards;

use app\classes\model\ActiveRecord;
use app\models\ClientContract;
use app\models\rewards\RewardClientContractService;
use app\modules\uu\models\ResourceModel;

/** Class RewardClientContractResource
 * 
 * @property int $contract_id
 * @property int $resource_id
 * @property int $price_percent
 * @property int $percent_margin_fee
 * @property int $reward_service_id
 * 
 * @property-read ClientContract $contract
 * @property-read ResourceModel $resourceModel
 * @property-read RewardClientContractService $rewardClientContractService
 */
class RewardClientContractResource extends ActiveRecord
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['resource_id', 'price_percent', 'percent_margin_fee','reward_service_id'], 'integer'],
        ];
    }

    public static function tableName()
    {
        return 'reward_client_contract_resource';
    }

    public function getClientContract() 
    {
        return $this->hasOne(ClientContract::class, ['id' => 'contract_id']);
    }

    public function getResourceModel() 
    {
        return $this->hasOne(ResourceModel::class, ['id' => 'resource_id']);
    }

    public function getRewardClientContractService()
    {
        return $this->hasOne(RewardClientContractService::class, ['id' => 'reward_service_id']);
    }

}
