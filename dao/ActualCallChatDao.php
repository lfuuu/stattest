<?php

namespace app\dao;


use app\classes\Singleton;
use app\models\ActualCallChat;
use app\models\ClientAccount;
use app\models\UsageCallChat;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @method static ActualCallChatDao me($args = null)
 */
class ActualCallChatDao extends Singleton
{
    public function collectFromUsages($usageId = null)
    {
        $query = (new Query)
            ->select([
                'usage_id' => 'u.id',
                'client_id' => 'c.id',
                'tarif_id' => 'u.tarif_id',
            ])
            ->from(['u' => UsageCallChat::tableName()])
            ->innerJoin(ClientAccount::tableName() . ' c', '`c`.`client` = `u`.`client`')
            ->where([
                'between',
                new Expression("NOW()"),
                new Expression('activation_dt'),
                new Expression('expire_dt')
            ])
            ->orderBy(['u.id' => SORT_ASC])
            ->indexBy('usage_id');


        if ($usageId) {
            $query->where([
                'u.id' => $usageId
            ]);
        }

        return $query->all();
    }

    public function loadSaved($usageId = null)
    {
        $query = ActualCallChat::find()
            ->orderBy([
                'usage_id' => SORT_ASC
            ])
            ->indexBy('usage_id');

        if ($usageId) {
            $query->where([
                'usage_id' => $usageId
            ]);
        }

        return  $query
            ->asArray()
            ->all();
    }
}
