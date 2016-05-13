<?php
namespace app\queries;

use app\models\CurrencyRate;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * @method CurrencyRate[] all($db = null)
 * @property
 */
class CurrencyRateQuery extends ActiveQuery
{

    /**
     * @param int $currencyId
     * @return null|\yii\db\ActiveRecord
     */
    public function currency($currencyId)
    {
        return $this->andWhere(['currency' => $currencyId])->orderBy(['date' => SORT_DESC])->one();
    }

}