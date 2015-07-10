<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\Emails;
use app\models\ClientAccount;

class EmailsServiceDao extends Singleton implements ServiceDao
{

    public function getAllForClient($client)
    {
        return
            Yii::$app->db->createCommand("
                SELECT
                    e.*,
                    IF((e.`actual_from` <= NOW()) AND (e.`actual_to` > NOW()), 1, 0) AS actual,
                    COUNT(ew.id) AS count_filters
                FROM emails e
                        LEFT JOIN email_whitelist ew ON (
                            (ew.`domain` = e.`domain`) AND
                            ((ew.`local_part` = '') OR (ew.`local_part` = e.`local_part`)) AND
                            (ew.`sender_address` = '') AND
                            (ew.`sender_address_domain` = '')
                        )
                WHERE
                    e.`client` = '" . $client . "'
                GROUP BY e.`id`
            ")->queryAll();
    }

    public function getPossibleToTransfer(ClientAccount $client)
    {
        $now = new \DateTime();

        return
            Emails::find()
                ->client($client->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }
}
