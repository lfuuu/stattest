<?php

namespace app\queries;

use yii\db\ActiveQuery;

/**
 * @method TechCpeQuery[] all($db = null)
 * @property
 */
class TechCpeQuery extends ActiveQuery
{

    public function hideNotLinked()
    {
        return $this->andWhere('service = "" OR id_service = 0');
    }

    public function actual()
    {
        return $this->andWhere("cast(now() as date) between actual_from and actual_to");
    }

    public function client($client)
    {
        return $this->andWhere("client = :client", [":client" => $client]);
    }

}