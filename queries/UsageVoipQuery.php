<?php
namespace app\queries;

use app\models\ClientAccount;
use app\models\UsageVoip;
use yii\db\Query;

/**
 * @method UsageVoip[] all($db = null)
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

    /**
     * @param $accountId
     * @return $this
     * @internal param string $number
     */
    public function account($accountId)
    {
        return $this->client(ClientAccount::find()->where(['id' => $accountId])->select('client')->scalar()?:'none');
    }
}
