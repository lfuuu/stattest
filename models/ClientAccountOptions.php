<?php
namespace app\models;

use yii\db\ActiveRecord;

class ClientAccountOptions extends ActiveRecord
{

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