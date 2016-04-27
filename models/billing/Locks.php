<?php

namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

class Locks extends ActiveRecord
{

    public static function tableName()
    {
        return 'billing.locks';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function find()
    {
        $query = parent::find();

        return
            $query->addSelect([
                'voip_auto_disabled',
                'voip_auto_disabled_local',
            ]);
    }

}