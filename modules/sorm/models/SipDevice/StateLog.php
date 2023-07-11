<?php

namespace app\modules\sorm\models\SipDevice;

use app\classes\model\ActiveRecord;
use app\modules\sorm\classes\sipDevice\behavior\MakeReducedStateBehavior;

/**
 * Class SipDeviceState
 *
 * @property string $id
 * @property string $created_at
 * @property string $is_add
 * @property string $account_id
 * @property string $region_id
 * @property string $did
 * @property string $ndc_type_id
 * @property string $sip_login
 */
class StateLog extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'sorm_sipdevice_state_log';
    }


    public function rules()
    {
        $rules = [[[
            'account_id',
            'region_id',
            'did',
            'ndc_type_id',
            'sip_login',
            'created_at',
        ], 'safe']];

        return $rules;
    }

    public function behaviors()
    {
        return [
            'MakeReducedStateBehavior' => MakeReducedStateBehavior::class,
        ];
    }

}
