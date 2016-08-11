<?php

namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property boolean $voip_auto_disabled - не используется
 * @property boolean $voip_auto_disabled_local - не используется
 * @property boolean $is_overran - суточная / месячная блокировка
 * @property boolean $is_finance_block - финансовая блокировка
 */
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