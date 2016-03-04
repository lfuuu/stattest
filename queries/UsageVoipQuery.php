<?php
namespace app\queries;

use app\models\UsageVoip;
use app\models\ClientAccount;

/**
 * @method UsageVoip[] all($db = null)
 * @property
 */
class UsageVoipQuery extends UsageQuery
{
    public function phone($number)
    {
        return $this->andWhere(["E164" => $number]);
    }
}
