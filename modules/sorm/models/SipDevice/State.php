<?php

namespace app\modules\sorm\models\SipDevice;

use app\classes\model\ActiveRecord;
use app\modules\sorm\classes\sipDevice\behavior\MakeLogBehavior;

/**
 * Class SipDeviceState
 *
 * @property string $account_id
 * @property string $region_id
 * @property string $did
 * @property string $ndc_type_id
 * @property string $sip_login
 * @property string $created_at
 */
class State extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'sorm_sipdevice_state';
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
            'MakeLogBehavior' => MakeLogBehavior::class,
        ];
    }


    public function getStateKey(): string
    {
        return implode("\t", [$this->account_id, $this->did, $this->sip_login, $this->created_at]);
    }

    public function fixLoad()
    {
        if (!$this->created_at) {
            return true;
        }

        $pos = array_filter([strpos($this->created_at, '+'), strpos($this->created_at, '.')]);

        if (!$pos) {
            return true;
        }

        $pos = min($pos);

        $this->created_at = substr($this->created_at, 0, $pos);

        return true;
    }
}
