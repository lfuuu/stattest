<?php

namespace app\modules\sim\columns\PhoneHistory;

use app\classes\grid\column\DataColumn;
use app\helpers\DateTimeZoneHelper;
use kartik\grid\GridView;
use yii\db\ActiveQuery;

class DateColumn extends DataColumn
{
    public $filterType = GridView::FILTER_DATE;

    public static function specifyQuery(ActiveQuery $query, $field, $date)
    {
        $dateFrom = new \DateTimeImmutable($date, new \DateTimeZone('UTC'));
        $dateTo = new \DateTimeImmutable($date, new \DateTimeZone('UTC'));

        $dateFrom = $dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
        $dateTo = $dateTo->format(DateTimeZoneHelper::DATE_FORMAT);

        $query->andWhere(['BETWEEN', $field, $dateFrom, $dateTo]);
    }

    /**
     * DateColumn constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = '';
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' sim-phones-history-' . $this->attribute . '-column';
    }

}