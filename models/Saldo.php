<?php
namespace app\models;

use yii\db\ActiveRecord;

class Saldo extends ActiveRecord
{

    public static function tableName()
    {
        return 'newsaldo';
    }

    /**
     * @param $clientId
     * @return \app\models\Saldo
     */
    public static function getLastSaldo($clientId)
    {
        return
            self::find()
                ->select([
                    'ts',
                    'saldo'
                ])
                ->where(['client_id' => $clientId])
                ->orderBy([
                    'ts' => SORT_DESC,
                    'id' => SORT_DESC,
                ])
                ->one();
    }

}