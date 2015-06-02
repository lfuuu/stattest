<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\UsageSms;
use app\models\ClientAccount;

class SmsServiceDao extends Singleton implements ServiceDao
{

    public function getAll()
    {
        return
            Yii::$app->db->createCommand("
                SELECT
                    s.*,
                    t.*,
                    s.`id` AS id,
                    c.`status` AS client_status,
                    IF((s.`actual_from` <= NOW()) AND (s.`actual_to` > NOW()), 1, 0) AS actual,
                    IF((s.`actual_from` <= (NOW() + INTERVAL 5 DAY)), 1, 0) AS actual5d
                FROM usage_sms s
                        LEFT JOIN clients c ON (c.`client` = s.`client`)
                        LEFT JOIN tarifs_sms t ON t.`id` = s.`tarif_id`
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
                    s.`id` AS id,
                    IF((s.`actual_from` <= NOW()) AND (s.`actual_to` > NOW()), 1, 0) AS actual,
                    IF((s.`actual_from` <= (NOW() + INTERVAL 5 DAY)), 1, 0) AS actual5d
                FROM usage_sms s
                        LEFT JOIN tarifs_sms t ON t.`id` = s.`tarif_id`
                WHERE s.`client` = '" . $client . "'
            ")->queryAll();
    }

    public function getPossibleToTransfer(ClientAccount $client)
    {
        $now = new \DateTime();

        return
            UsageSms::find()
                ->andWhere(['client' => $client->client])
                ->andWhere('actual_from <= :date', [':date' => $now->format('Y-m-d')])
                ->andWhere(['dst_usage_id' => 0])
                ->all();
    }

}