<?php
namespace app\queries;

use app\models\UsageVoip;

/**
 * @method UsageVoip[] all($db = null)
 * @property
 */
class UsageVoipQuery extends UsageQuery
{

    /**
     * @param string $number
     * @return $this
     */
    public function phone($number)
    {
        return $this->andWhere(['E164' => $number]);
    }

}
