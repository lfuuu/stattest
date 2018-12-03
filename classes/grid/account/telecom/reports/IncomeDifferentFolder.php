<?php
namespace app\classes\grid\account\telecom\reports;

use app\classes\grid\account\AccountGridFolder;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use Yii;
use yii\db\Query;


/**
 * Class IncomeDifferentFolder
 */
class IncomeDifferentFolder extends AccountGridFolder
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Расхождение';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            'id',
            'company',
            'account_manager',
            'manager',
            'legal_entity',
            'service',
            'bill_date',
            'abon',
            'abon1',
            'over',
            'over1',
            'abondiff',
            'overdiff',
        ];
    }

    /**
     * @return null
     */
    public function getCount()
    {
        return null;
    }

    /**
     * @param Query $query
     */
    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        list($dateFrom, $dateTo) = preg_split('/[\s+]\-[\s+]/', $this->bill_date);

        if (!$dateTo) {
            $dateTo = date('Y-m-t');
        }

        $monthThisEndDate = (new \DateTimeImmutable($dateTo))->modify('last day of this month');
        $monthThisStartDate = $monthThisEndDate->modify('first day of this month');

        $monthPrevStartDate = $monthThisStartDate->modify('-1 month');
        $monthPrevEndDate = $monthPrevStartDate->modify('last day of this month');

        $monthThisStart = $monthThisStartDate->format(DateTimeZoneHelper::DATE_FORMAT);
        $monthThisEnd = $monthThisEndDate->format(DateTimeZoneHelper::DATE_FORMAT);
        $monthPrevStart = $monthPrevStartDate->format(DateTimeZoneHelper::DATE_FORMAT);
        $monthPrevEnd = $monthPrevEndDate->format(DateTimeZoneHelper::DATE_FORMAT);

        if ($dateFrom != $monthPrevStart) {
            $this->bill_date = $monthPrevStart.' - '.$monthThisEnd;
        }

        $query->addSelect([
            'l.service',
            'b.bill_date',
        ]);

        $columns = [
            'abon' => "IF(b.bill_date BETWEEN '{$monthPrevStart}' AND '{$monthPrevEnd}' AND l.date_from BETWEEN  '{$monthPrevStart}' and '{$monthPrevEnd}', l.sum, 0)", // Абонентка за предыдущий месяц
            'abon1' => "IF(b.bill_date BETWEEN '{$monthThisStart}' AND '{$monthThisEnd}' AND l.date_from BETWEEN '{$monthThisStart}' and '{$monthThisEnd}', l.sum, 0)", // Абонентка за текущий месяц
            'over' => "IF (b.bill_date BETWEEN '{$monthPrevStart}' AND '{$monthPrevEnd}' AND l.date_from < '{$monthPrevStart}', l.sum, 0)", // Ресурсы за пред период
            'over1' => "IF (b.bill_date BETWEEN '{$monthThisStart}' AND '{$monthThisEnd}' AND l.date_from BETWEEN  '{$monthPrevStart}' and '{$monthPrevEnd}', l.sum, 0)", // Ресурсы за текущий период
        ];

        $query->addSelect([
            'abon' => 'SUM(' . $columns['abon'] . ')',
            'abon1' => 'SUM(' . $columns['abon1'] . ')',
            'abondiff' => 'SUM(' . $columns['abon1'] . ' - ' . $columns['abon'] . ')',
            'over' => 'SUM(' . $columns['over'] . ')',
            'over1' => 'SUM(' . $columns['over1'] . ')',
            'overdiff' => 'SUM(' . $columns['over1'] . ' - ' . $columns['over'] . ')',
        ]);

        $query->join('INNER JOIN', 'newbills b', 'c.id=b.client_id and biller_version = ' . ClientAccount::VERSION_BILLER_USAGE);
        $query->join('INNER JOIN', 'newbill_lines l', 'l.bill_no=b.bill_no');

        if (!$this->hasServiceSignature(static::SERVICE_FILTER_GOODS) && !$this->hasServiceSignature(static::SERVICE_FILTER_EXTRA)) {
            $query->andWhere('l.type = "service"');
            $query->andWhere(['not in', 'l.service', ['1C', 'bill_monthlyadd', '', 'all4net']]);
        }

        $query->groupBy(['l.service', 'c.id',]);
    }

    /**
     * @return string
     */
    public function queryOrderBy()
    {
        return 'over DESC';
    }


}