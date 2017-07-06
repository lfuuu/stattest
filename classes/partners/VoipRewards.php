<?php

namespace app\classes\partners;

use app\classes\Assert;
use app\classes\partners\rewards\EnableReward;
use app\classes\partners\rewards\EnablePercentageReward;
use app\classes\partners\rewards\MarginPercentageReward;
use app\classes\partners\rewards\MonthlyFeePercentageReward;
use app\classes\partners\rewards\ResourcePercentageReward;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;
use yii\base\Model;

class VoipRewards extends Model implements RewardsInterface
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
            EnableReward::class,
            EnablePercentageReward::class,
            MonthlyFeePercentageReward::class,
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
                $service = UsageVoip::findOne(['id' => $serviceId]);
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
        $service = $this->getService($serviceId);

        switch ($this->clientAccountVersion) {
            case ClientAccount::VERSION_BILLER_USAGE: {
                return $service->ndc_type_id === NdcType::ID_FREEPHONE;
            }

            case ClientAccount::VERSION_BILLER_UNIVERSAL: {
                // @todo universal service
                return false;
            }
        }

        return false;
    }

}