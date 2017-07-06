<?php

namespace app\classes\partners;

use app\classes\Assert;
use app\classes\partners\rewards\MarginPercentageReward;
use app\classes\partners\rewards\ResourcePercentageReward;
use app\models\ClientAccount;
use app\models\UsageTrunk;
use yii\base\Model;

class TrunkRewards extends Model implements RewardsInterface
{

    /**
     * @var int
     */
    public $clientAccountVersion = ClientAccount::VERSION_BILLER_USAGE;

    /**
     * @return array
     */
    public function getAvailableRewards()
    {
        return [
            ResourcePercentageReward::class,
            MarginPercentageReward::class,
        ];
    }

    /**
     * @param int $serviceId
     * @return mixed
     */
    public function getService($serviceId)
    {
        $service = null;

        switch ($this->clientAccountVersion) {
            case ClientAccount::VERSION_BILLER_USAGE: {
                $service = UsageTrunk::findOne(['id' => $serviceId]);
                break;
            }

            case ClientAccount::VERSION_BILLER_UNIVERSAL: {
                // @todo universal service
                return null;
            }
        }

        Assert::isObject($service);

        return $service;
    }

    /**
     * @param int $serviceId
     * @return bool
     */
    public function isExcludeService($serviceId)
    {
        return false;
    }

}