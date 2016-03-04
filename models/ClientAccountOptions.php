<?php
namespace app\models;

use yii\db\ActiveRecord;

class ClientAccountOptions extends ActiveRecord
{

    const OPTION_MAIL_DELIVERY = 'mail_delivery_variant';

    const OPTION_MAIL_DELIVERY_DEFAULT_VALUE = 'undefined';

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