<?php

namespace app\models\filter;
use app\classes\model\ActiveRecord;
use app\classes\traits\AddClientAccountFilterTraits;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\DataRaw;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class DataRawSearch extends ActiveRecord
{
    use AddClientAccountFilterTraits;
    private $allCost;
    private $allQuantity;

    const MNC = 'mnc';
    const MCC = 'mcc';
    const HOUR = 'hour';
    const DAY = 'day';
    const MONTH = 'month';
    const YEAR = 'year';
    const DAY_IN_SECONDS = 86400;

    public $mcc;
    public $network;
    public $fromDate;
    public $toDate;
    public $groupBy;

    public function rules()
    {
        return [
            [['fromDate', 'mcc', 'network', 'groupBy', 'toDate'], 'safe'],
        ];
    }

    /**
     * Получить общую стоимость
     *
     * @return float
     */
    public function getAllCost()
    {
        return $this->allCost;
    }

    /**
     * Получить общее количество
     *
     * @return float
     */
    public function getAllQuantity()
    {
        return $this->allQuantity;
    }

    /**
     * Добавляет select в query
     *
     * @param ActiveQuery $query
     * @param string $dateFormat
     */
    public function addSelect(&$query, $dateFormat)
    {
        $sumQuantity = "sum(quantity) as quantity";
        $sumCost = "sum(cost) as cost";
        $chargeTime = "to_char(data_raw.charge_time, '$dateFormat')";
        if ($dateFormat == "yyyy-mm-dd hh") {
            $chargeTime .= " || '-' || to_char(charge_time + interval '1h', 'hh')";
        }

        $query->addSelect([$chargeTime . ' as charge_time', $sumCost, $sumQuantity]);
        $query->groupBy([$chargeTime]);
        $query->orderBy([$chargeTime => SORT_ASC]);
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $accountId = $this->_getCurrentClientAccountId();
        $query = DataRaw::find()->where(['account_id' => $accountId]);

        $this->setAttributes($params);

        switch ($this->groupBy) {
            case static::MNC:
                $query->addSelect('data_raw.mnc, data_raw.mcc, sum(cost) as cost, sum(quantity) as quantity');
                $query->addGroupBy(['data_raw.mnc', 'data_raw.mcc']);
                break;
            case static::MCC:
                $query->addSelect('data_raw.mcc, sum(cost) as cost, sum(quantity) as quantity');
                $query->groupBy('data_raw.mcc');
                break;
            case static::HOUR:
                $this->addSelect($query, 'yyyy-mm-dd hh');
                break;
            case static::DAY:
                $this->addSelect($query, 'yyyy-mm-dd');
                break;
            case static::MONTH:
                $this->addSelect($query, 'yyyy-mm');
                break;
            case static::YEAR:
                $this->addSelect($query, 'yyyy');
                break;
        }

        if ($this->fromDate) {
            $query->andWhere(['>', 'charge_time', $this->fromDate]);
        }
        if ($this->toDate) {
            $query->andWhere(['<', 'charge_time', date(DateTimeZoneHelper::DATE_FORMAT, strtotime($this->toDate) + static::DAY_IN_SECONDS)]);
        }

        $query->joinWith('mccModel');
        $query->joinWith('mncModel');

        if (!empty($this->mcc)) {
            $query->andWhere(['data_raw.mcc' => $this->mcc]);
        }
        if (!empty($this->network)) {
            list($mnc, $mcc) = explode(':', $this->network);
            $query->andWhere(['data_raw.mnc' => $mnc, 'data_raw.mcc' => $mcc]);
        }

        $queryClone = clone $query;
        $arr = $queryClone->select(['sum(cost) as all_cost', 'sum(quantity) as all_quantity'])->asArray()->one();
        $this->allCost = $arr['all_cost'];
        $this->allQuantity = $arr['all_quantity'];

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }
}
