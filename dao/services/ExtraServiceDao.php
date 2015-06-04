<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\UsageExtra;

class ExtraServiceDao extends Singleton implements ServiceDao
{

    public function getAllForClient($client)
    {
        return
            Yii::$app->db->createCommand("
                SELECT
                    t.*,
                    s.*,
                    IF((s.`actual_from` <= NOW()) AND (s.`actual_to` > NOW()), 1, 0) AS actual,
                    IF((s.`actual_from` <= (NOW() + INTERVAL 5 DAY)), 1, 0) AS actual5d
                FROM usage_extra s
                        INNER JOIN tarifs_extra t
                          ON
                            t.`id` = s.`tarif_id` AND
                            t.`status` IN ('public','special','archive') AND
                            t.`code` NOT IN ('welltime','wellsystem')
                    where
                        s.`client` = '" . $client . "'
            ")->queryAll();
    }

    public function getPossibleToTransfer(ClientAccount $client)
    {
        $now = new \DateTime();

        return
            UsageExtra::find()
                ->innerJoinWith('tariff', false)
                ->andWhere(['client' => $client->client])
                ->andWhere('actual_from <= :date', [':date' => $now->format('Y-m-d')])
                ->andWhere(['next_usage_id' => 0])
                ->andWhere(['tarifs_extra.status' => ['public','special','archive']])
                ->andWhere(['not in', 'tarifs_extra.code', ['welltime','wellsystem']])
                ->all();
    }

}