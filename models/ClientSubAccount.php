<?php
namespace app\models;

use app\classes\model\HistoryActiveRecord;
use app\classes\validators\AccountIdValidator;
use app\helpers\DateTimeZoneHelper;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * Class ClientSubAccount
 *
 * @property int $id
 * @property int $account_id
 * @property string $vpbx_subaccount
 * @property int $stat_product_id
 * @property string $number
 * @property float $balance
 * @property float $credit
 * @property int $amount_date
 * @property int $voip_limit_month
 * @property int $voip_limit_day
 * @property int $voip_limit_mn_day
 * @property int $voip_limit_mn_month
 * @property int $is_voip_orig_disabled
 * @property int $is_voip_blocked
 * @property string $did
 */
class ClientSubAccount extends HistoryActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_subaccount';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['id', 'required', 'on' => ['save', 'delete']],
            ['account_id', AccountIdValidator::className(), 'skipOnEmpty' => true],
            [
                [
                    'id',
                    'sub_account',
                    'number',
                    'credit',
                    'voip_limit_month',
                    'voip_limit_day',
                    'voip_limit_mn_day',
                    'voip_limit_mn_month',
                    'stat_product_id',
                ],
                'integer',
                'min' => 0
            ],
            [
                [
                    'is_voip_orig_disabled',
                    'is_voip_blocked'
                ],
                'boolean'
            ],
            [['name', 'did'], 'string'],
            [['balance', 'credit'], 'number'],
            ['amount_date', 'datetime', 'format' => 'php:' . DateTimeZoneHelper::DATETIME_FORMAT]
        ];
    }
}
