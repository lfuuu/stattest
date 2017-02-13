<?php
namespace app\queries;

use app\helpers\DateTimeZoneHelper;
use yii\db\ActiveQuery;

class CurrencyRateQuery extends ActiveQuery
{

    /**
     * @param int $currencyId
     * @return $this
     */
    public function currency($currencyId)
    {
        return $this->andWhere(['currency' => $currencyId])->orderBy(['date' => SORT_DESC]);
    }

    /**
     * @param string $date
     * @return $this
     */
    public function onDate($date = null)
    {
        if ($date instanceof \DateTime) {
            $this->andWhere(['date' => $date->format(DateTimeZoneHelper::DATE_FORMAT)]);
        } elseif (is_string($date) && !empty($date)) {
            $this->andWhere(['date' => (new \DateTime($date))->format(DateTimeZoneHelper::DATE_FORMAT)]);
        }

        return $this;
    }

}