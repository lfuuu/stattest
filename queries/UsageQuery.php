<?php
namespace app\queries;

use app\models\UsageEmails;
use app\models\UsageTechCpe;
use yii\db\ActiveQuery;
use yii\db\Expression;

class UsageQuery extends ActiveQuery
{

    /**
     * Услуга актуальна
     *
     * @return $this
     */
    public function actual()
    {
        if (in_array($this->modelClass, [UsageEmails::class])) {
            return $this->andWhere('CAST(NOW() AS date) BETWEEN actual_from AND actual_to');
        } else {
            return $this->andWhere('now() between activation_dt and expire_dt');
        }

    }

    /**
     * Услуга будет включена в будущем
     *
     * @return $this
     */
    public function inFuture()
    {
        return $this->andWhere(['>', 'actual_from', new Expression('CAST(NOW() AS date)')]);
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
