<?php

namespace app\forms\rewards;

use app\models\rewards\RewardClientContractResource;
use app\models\rewards\RewardClientContractService;

class RewardClientContractFormEdit extends RewardClientContractForm
{
    /**
     * Конструктор
     */
    public function init()
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter id'));
        }

        parent::init();
    }

    /**
     * @return RewardClientContractService[]
     */
    public function getRewardClientContractServices()
    {
        return RewardClientContractService::findAll(['client_contract_id' => $this->id]);
    }

    /**
     * @return RewardClientContractResource[]
     */
    public function getRewardClientContractResources()
    {
        return RewardClientContractResource::findAll(['reward_service_id' => $this->serviceRewards['id']]);
    }
}
