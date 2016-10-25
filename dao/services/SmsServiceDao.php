<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;

class SmsServiceDao extends Singleton
{

    /**
     * @return array
     */
    public function getAll()
    {
        return
            Yii::$app->db->createCommand("
                SELECT
                    t.*,
                    s.*,
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

    /**
     * @param string $client
     * @return array
     */
    public function getAllForClient($client)
    {
        return
            Yii::$app->db->createCommand("
                SELECT
                    t.*,
                    s.*,
                    s.`id` AS id,
                    IF((s.`actual_from` <= NOW()) AND (s.`actual_to` > NOW()), 1, 0) AS actual,
                    IF((s.`actual_from` <= (NOW() + INTERVAL 5 DAY)), 1, 0) AS actual5d
                FROM usage_sms s
                        LEFT JOIN tarifs_sms t ON t.`id` = s.`tarif_id`
                WHERE s.`client` = '" . $client . "'
            ")->queryAll();
    }

}
