<?php
namespace app\queries;

use yii\db\ActiveQuery;

class UsageQuery extends ActiveQuery
{

    /**
     * @return $this
     */
    public function actual()
    {
        return $this->andWhere('CAST(NOW() AS date) BETWEEN actual_from AND actual_to');
    }

    /**
     * @param string $client
     * @return $this
     */
    public function client($client)
    {
        return $this->andWhere(['client' => $client]);
    }

}
