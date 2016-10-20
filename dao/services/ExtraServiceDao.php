<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;

class ExtraServiceDao extends Singleton
{

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

}
