<?php

namespace app\classes\partners\handler;

use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use yii\base\Model;

abstract class AHandler extends Model
{
    /**
     * @var int
     */
    public $clientAccountVersion = ClientAccount::VERSION_BILLER_USAGE;

    /**
     * @return array
     */
    abstract public function getAvailableRewards();

    /**
     * @param int $serviceId
     * @return ActiveRecord
     */
    abstract public function getService($serviceId);

    /**
     * @param int $serviceId
     * @return bool
     */
    abstract public function isExcludeService($serviceId);
}