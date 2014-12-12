<?php
namespace app\models;

use yii\db\ActiveRecord;

class NumberCreateParams extends ActiveRecord
{
    public static function tableName()
    {
        return 'number_create_params';
    }

    public static function getParams($number)
    {
        $params = ["type_connect" => "line", "sip_accounts" => 1]; //by default

        $n = NumberCreateParams::findOne(["number" => $number]);

        if ($n)
        {
            $params = json_decode($n->params);           
        }

        return $params;
    }
}
