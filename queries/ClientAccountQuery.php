<?php
namespace app\queries;

use app\models\ClientAccount;
use yii\db\ActiveQuery;

/**
 * @method ClientAccount[] all($db = null)
 */
class ClientAccountQuery extends ActiveQuery
{
    /**
     * @return static the query object itself
     */
    public function active()
    {
        return $this->andWhere(['is_active' => 1]);
    }

    /**
     * @return static the query object itself
     */
    public function prepayment()
    {
        return $this->andWhere(['>', 'credit', -1]);
    }

}