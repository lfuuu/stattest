<?php

namespace app\models\rewards;

use app\classes\model\ActiveRecord;
use app\models\ClientContract;
use app\models\rewards\RewardClientContractResource;
use app\models\rewards\RewardsServiceTypeResource;
use app\models\User;
use app\modules\uu\models\ServiceType;


/**
 * Class ClientAccount
 * 
 * @property int $id
 * @property int $contract_id
 * @property int $service_type_id
 * @property int $percentage_once_only
 * @property int $percentage_of_fee
 * @property int $once_only
 * @property int $period_month
 * @property string $actual_from
 * @property string $insert_time
 * @property string $period_type
 * 
 * @property-read ServiceType $serviceType
 * @property-read ClientContract $contract
 * @property-read RewardClientContractResource[] $resources
 * @property-read RewardsServiceTypeResource[] $activeResources
 * @property-read User $user
**/

class RewardClientContractService extends ActiveRecord
{

    const PERIOD_ALWAYS = 'always';
    const PERIOD_MONTH = 'month';

    public static $periods = [
        self::PERIOD_ALWAYS => 'Всегда',
        self::PERIOD_MONTH => 'Месяц',
    ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'id',
                    'client_contract_id',
                    'service_type_id',
                    'percentage_once_only',
                    'percentage_of_fee',
                    'percentage_of_minimal',
                    'once_only',
                    'period_month',
                    'user_id',
                ], 'integer'
            ],
            [['actual_from'], 'string'],
            [['insert_time'], 'string'],
            [['period_type'], 'default', 'value' => self::PERIOD_ALWAYS],
        ];
    }


    public static function tableName()
    {
        return 'reward_client_contract_service';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceType() {
        return $this->hasOne(ServiceType::class, ['id' => 'service_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientContract() {
        return $this->hasOne(ClientContract::class, ['id' => 'contract_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResources()
    {
        return $this->hasMany(RewardClientContractResource::class, ['reward_service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActiveResources()
    {
        return $this->hasMany(RewardsServiceTypeResource::class, ['service_type_id' => 'service_type_id']);
    }

     /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
