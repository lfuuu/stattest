<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * Class Locks
 *
 * @property boolean $client_id
 * @property boolean $voip_auto_disabled - не используется
 * @property boolean $voip_auto_disabled_local - не используется
 * @property boolean $is_overran - суточная блокировка
 * @property boolean $is_mn_overran - суточная МН блокировка
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
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * Получение последней блокировки
     *
     * @return array [b_voip_auto_disabled, b_voip_auto_disabled_local, b_is_overran, b_is_mn_overran, b_is_finance_block]
     * @throws \yii\db\Exception
     */
    public function getLastLock()
    {
        $sql = sprintf('SELECT * FROM billing.locks_get(%d)', $this->client_id);
        return self::getDb()->createCommand($sql)->queryOne();
    }
}