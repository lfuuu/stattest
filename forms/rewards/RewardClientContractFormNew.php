<?php

namespace app\forms\rewards;


use app\models\rewards\RewardClientContractResource;
use app\models\rewards\RewardClientContractService;

class RewardClientContractFormNew extends RewardClientContractForm
{
    public function getRewardClientContractServiceModel()
    {
        $model = new RewardClientContractService();

        return $model;
    }

    /**
     * @return RewardClientContractService[]
     */
    public function getRewardClientContractServices()
    {
    }

    /**
     * @return RewardClientContractResource[]
     */
    public function getRewardClientContractResources()
    {
    }
}
