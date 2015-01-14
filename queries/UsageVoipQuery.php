<?php
namespace app\queries;

use app\models\UsageVoip;
use yii\db\ActiveQuery;

/**
 * @method UsageVoip[] all($db = null)
 * @property
 */
class UsageVoipQuery extends ActiveQuery
{
    public function phone($number)
    {
        return $this->andWhere(["E164" => $number]);
    }

    public function actual()
    {
        return $this->andWhere("cast(now() as date) between actual_from and actual_to");
    }
}
