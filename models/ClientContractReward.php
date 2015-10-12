<?php
namespace app\models;

use yii\db\ActiveRecord;

class ClientContractReward extends ActiveRecord
{

    const PERIOD_ALWAYS = 'always';
    const PERIOD_MONTH = 'month';

    const USAGE_VOIP = 'voip';
    const USAGE_VIRTPBX = 'virtpbx';

    public static $usages = [
        self::USAGE_VOIP => 'Параметры вознаграждения - IP телефония',
        self::USAGE_VIRTPBX => 'Параметры вознаграждения - ВАТС',
    ];

    public static function tableName()
    {
        return 'client_contract_reward';
    }

    public function rules()
    {
        return [
            [['contract_id', 'usage_type', 'period_type'], 'required'],
            [['contract_id', 'once_only', 'percentage_of_fee', 'percentage_of_over','period_month'], 'integer', 'integerOnly' => true],
            ['period_type', 'in', 'range' => [self::PERIOD_ALWAYS, self::PERIOD_MONTH]],
            ['usage_type', 'in', 'range' => [self::USAGE_VOIP, self::USAGE_VIRTPBX]],

            [['once_only', 'percentage_of_fee', 'percentage_of_over','period_month'], 'default', 'value' => 0],
            [['period_type'], 'default', 'value' => self::PERIOD_ALWAYS],
        ];
    }
}