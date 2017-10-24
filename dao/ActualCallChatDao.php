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
        $queryUsage = (new Query)
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
            ]);

        $queryUU = AccountTariff::find()
            ->alias('u')
            ->select([
                'usage_id' => 'id',
                'client_id' => 'client_account_id',
                'tarif_id' => new Expression(UsageCallChat::DEFAULT_TARIFF_ID),
            ])
            ->where(['service_type_id' => ServiceType::ID_CALL_CHAT])
            ->andWhere(['IS NOT', 'tariff_period_id', null])
            ->orderBy(['u.id' => SORT_ASC]);

        $query = (new Query())
            ->from(['a' => $queryUsage->union($queryUU)])
            ->orderBy(['usage_id' => SORT_ASC])
            ->indexBy('usage_id');


        if ($usageId) {
            $query->where([
                'usage_id' => $usageId
            ]);
        }

        return $query
            ->createCommand()
            ->queryAll();
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

        return $query
            ->createCommand()
            ->queryAll();
    }
}
