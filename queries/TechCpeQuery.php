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

}