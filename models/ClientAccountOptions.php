<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class ClientAccountOptions
 * @package app\models
 * @property int $client_account_id
 * @property string $option
 * @property string $value
 */
class ClientAccountOptions extends ActiveRecord
{

    const OPTION_MAIL_DELIVERY = 'mail_delivery_variant';
    const OPTION_MAIL_DELIVERY_DEFAULT_VALUE = 'undefined';
    const OPTION_MAIL_DELIVERY_LANGUAGE = 'mail_delivery_language';

    const OPTION_VOIP_CREDIT_LIMIT_DAY_WHEN = 'voip_credit_limit_day_when';
    const OPTION_VOIP_CREDIT_LIMIT_DAY_VALUE = 'voip_credit_limit_day_value';

    const OPTION_VOIP_CREDIT_LIMIT_DAY_MN_WHEN = 'voip_credit_limit_day_mn_when';
    const OPTION_VOIP_CREDIT_LIMIT_DAY_MN_VALUE = 'voip_credit_limit_day_mn_value';

    public static function tableName()
    {
        return 'client_account_options';
    }

    public function rules()
    {
        return [
            [['client_account_id',], 'integer'],
            [['option', 'value',], 'string'],
            [['client_account_id', 'option', 'value'], 'required'],
        ];
    }

}