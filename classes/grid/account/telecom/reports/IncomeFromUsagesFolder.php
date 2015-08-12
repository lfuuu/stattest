<?php
namespace app\classes\grid\account\telecom\reports;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class IncomeFromUsagesFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Доход по услугам';
    }

    public function getColumns()
    {
        return [
            'service',
            'region',
            'abon',
            'over',
            'total',
            'bill_date',
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->select = array_merge($query->select, [
            'l.service',
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0,l.sum,0)) AS abon',
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11,l.sum,0)) AS over',
            'sum(l.sum) as total',
            'b.bill_date',
        ]);

        $query->join('INNER JOIN', 'newbills b', 'c.id=b.client_id');
        $query->join('INNER JOIN', 'newbill_lines l', 'l.bill_no=b.bill_no');

        $query->andWhere('b.is_payed = 1');
        $query->andWhere('l.type = "service"');
        $query->andWhere(['not in', 'l.service', ['1C', 'bill_monthlyadd', '', 'all4net']]);

        list($dateFrom, $dateTo) = explode(' - ', $this->bill_date);
        if (!$dateFrom)
            $dateFrom = date('Y-m-01');
        if (!$dateTo)
            $dateTo = date('Y-m-t');

        $query->andWhere('b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND  :date_to', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);

        $query->groupBy(['l.service']);
    }

    public function queryOrderBy()
    {
        return 'over DESC';
    }


}