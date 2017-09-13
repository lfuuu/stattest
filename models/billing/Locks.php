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
     * @param bool|string $field
     * @return ClientLockLogs
     */
    public function getLastLock($field = true)
    {
        /** @var ClientLockLogs $clientLockLogs */
        $clientLockLogs = ClientLockLogs::find()
            ->where([
                'client_id' => $this->client_id,
                $field => true
            ])
            ->orderBy(['dt' => SORT_DESC])
            ->one();
        return $clientLockLogs;
    }
}