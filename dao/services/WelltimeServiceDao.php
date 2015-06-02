<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\UsageWelltime;
use app\models\ClientAccount;

class WelltimeServiceDao extends Singleton implements ServiceDao
{

    public function getAll()
    {
        return
            Yii::$app->db->createCommand("
                SELECT
                    s.*,
                    t.*,
                    c.`status` AS client_status,
                    IF((s.`actual_from` <= NOW()) AND (s.`actual_to` > NOW()), 1, 0) AS actual,
                    IF((s.`actual_from` <= (NOW() + INTERVAL 5 DAY)), 1, 0) AS actual5d
                FROM usage_welltime s
                        LEFT JOIN clients c ON (c.`client` = s.`client`)
                        INNER JOIN tarifs_extra t ON t.`id` = s.`tarif_id` AND t.`code` in ('welltime')
                HAVING actual
                ORDER BY s.`client`, s.`actual_from`
            ")->queryAll();
    }

    public function getAllForClient($client)
    {
        return
            Yii::$app->db->createCommand("
                SELECT
                    s.*,
                    t.*,
                    IF((s.`actual_from` <= NOW()) AND (s.`actual_to` > NOW()), 1, 0) AS actual,
                    IF((s.`actual_from` <= (NOW() + INTERVAL 5 DAY)), 1, 0) AS actual5d
                FROM usage_welltime s
                        INNER JOIN tarifs_extra t ON t.`id` = s.`tarif_id` AND t.`code` in ('welltime')
                WHERE
                    s.`client` = '" . $client ."'
            ")->queryAll();
    }

    public function getPossibleToTransfer(ClientAccount $client)
    {
        $now = new \DateTime();

        return
            UsageWelltime::find()
                ->andWhere(['client' => $client->client])
                ->andWhere('actual_from <= :date', [':date' => $now->format('Y-m-d')])
                ->andWhere(['dst_usage_id' => 0])
                ->all();
    }

}