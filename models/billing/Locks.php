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
     * Получение блокировки
     *
     * @param int $clientAccountId
     * @return array [b_voip_auto_disabled, b_voip_auto_disabled_local, b_is_overran, b_is_mn_overran, b_is_finance_block, dt_last_dt]
     * @throws \yii\db\Exception
     */
    public static function getLock($clientAccountId)
    {
        $lock = \Yii::$app->cache->get('lock' . $clientAccountId);

        if ($lock) {
            return $lock;
        }

        if (\Yii::$app->cache->exists('lockcls')) {
            return [
                'b_voip_auto_disabled' => false,
                'b_voip_auto_disabled_local' => false,
                'b_is_overran' => false,
                'b_is_mn_overran' => false,
                'b_is_finance_block' => false,
                'dt_last_dt' => '',
            ];
        }

        $sql = sprintf('SELECT * FROM billing.locks_get(%d)', $clientAccountId);
        return self::getDb()->createCommand($sql)->queryOne();
    }

    /**
     * Получение клиентов с блокировками VOIP (voip_auto_disabled, voip_auto_disabled_local)
     *
     * @return int[]
     * @throws \yii\db\Exception
     */
    public static function getVoipLocks()
    {
        return self::getDb()->createCommand('SELECT * FROM billing.lock_clients_voip_get()')->queryColumn();
    }

    /**
     * Получение клиентов с финансовыми блокировками (is_finance_block, is_overran, is_mn_overran)
     *
     * @return int[]
     * @throws \yii\db\Exception
     */
    public static function getFinanceLocks()
    {
        return self::getDb()->createCommand('SELECT * FROM billing.lock_clients_finance_get()')->queryColumn();
    }
}