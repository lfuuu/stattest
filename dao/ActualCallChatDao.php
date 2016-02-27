<?php
namespace app\dao;


use app\classes\Singleton;
use app\models\ActualCallChat;
use app\models\ClientAccount;
use app\models\UsageCallChat;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * @method static ActualCallChatDao me($args = null)
 * @property
 */
class ActualCallChatDao extends Singleton
{
    public function collectFromUsages($usageId = null)
    {
        $query = (new \yii\db\Query)
            ->select([
                'usage_id' => 'u.id',
                'client_id' => 'c.id',
                'tarif_id' => 'u.tarif_id'
            ])
            ->from(['u' => UsageCallChat::tableName()])
            ->innerJoin(ClientAccount::tableName() . ' c', '`c`.`client` = `u`.`client`')
            ->where(['between', new Expression("NOW()"), new Expression('activation_dt'), new Expression('expire_dt')]) //надо что-то получше придумать...
            ->orderBy(['u.id' => SORT_ASC]);

        if ($usageId) {
            $query->andWhere([
                'u.id' => $usageId
            ]);
        }

        return
            ArrayHelper::index(
                $query
                    ->createCommand()
                    ->queryAll(),
                'usage_id'
            );
    }

    public function loadSaved($usageId = null)
    {
        $query = ActualCallChat::find()
            ->orderBy([
                'usage_id' => SORT_ASC
            ]);

        if ($usageId) {
            $query->where([
                'usage_id' => $usageId
            ]);
        }

        return
            ArrayHelper::index(
                $query
                    ->createCommand()
                    ->queryAll(),
                'usage_id'
            );
    }
}
