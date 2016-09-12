<?php

namespace app\models\billing;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property boolean $voip_auto_disabled - не используется
 * @property boolean $voip_auto_disabled_local - не используется
 * @property boolean $is_overran - суточная / месячная блокировка
 * @property boolean $is_finance_block - финансовая блокировка
 */
class Locks extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.locks';
    }

    /**
     * @return mixed
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * @param string|true $field
     * @return ActiveQuery
     */
    public function getLastLock($field = true)
    {
        return
            LockLogs::find()
                ->where([
                    'client_id' => 'client_id',
                    $field => true
                ])
                ->orderBy(['dt' => SORT_DESC])
                ->one();
    }

}