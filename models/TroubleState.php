<?php
namespace app\models;

use yii\db\ActiveRecord;

class TroubleState extends ActiveRecord
{
    const CONNECT__INCOME = 41;
    const CONNECT__NEGOTIATION = 42;
    const CONNECT__VERIFICATION_OF_DOCUMENTS = 49;

    public static function tableName()
    {
        return 'tt_stages';
    }
}

