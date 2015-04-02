<?php
namespace app\queries;

use yii\db\ActiveQuery;

class UsageQuery extends ActiveQuery
{
    public function actual()
    {
        return $this->andWhere("cast(now() as date) between actual_from and actual_to");
    }

    public function client($client)
    {
        return $this->andWhere("client = :client", ["client" => $client]);
    }
}
