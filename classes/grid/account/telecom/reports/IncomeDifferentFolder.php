<?php
namespace app\classes\grid\account\telecom\reports;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class IncomeDifferentFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Расхождение';
    }

    public function getColumns()
    {
        return [
            'id',
            'company',
            'account_manager',
            'manager',
            'region',
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

    public function getCount()
    {
        return null;
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        list($dateFrom, $dateTo) = explode(' - ', $this->bill_date);
        if (!$dateFrom)
            $dateFrom = date('Y-m-01');
        if (!$dateTo)
            $dateTo = date('Y-m-t');

        $query->select = array_merge($query->select, [
            'l.service',
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0 AND b.bill_date BETWEEN DATE_ADD( \''.$dateFrom.'\', INTERVAL -1 MONTH) AND DATE_ADD( \''.$dateTo.'\', INTERVAL -1 MONTH),l.sum,0)) AS abon',
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0 AND b.bill_date BETWEEN  \''.$dateFrom.'\' AND  \''.$dateTo.'\',l.sum,0)) AS abon1',
            'SUM(IF((MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11) AND b.bill_date BETWEEN DATE_ADD( \''.$dateFrom.'\', INTERVAL -1 MONTH) AND DATE_ADD( \''.$dateTo.'\', INTERVAL -1 MONTH),l.sum,0)) AS over',
            'SUM(IF((MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11) AND b.bill_date BETWEEN  \''.$dateFrom.'\' AND  \''.$dateTo.'\',l.sum,0)) AS over1',
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0 AND b.bill_date BETWEEN  \''.$dateFrom.'\' AND  \''.$dateTo.'\',l.sum,0))-SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0 AND b.bill_date BETWEEN DATE_ADD( \''.$dateFrom.'\', INTERVAL -1 MONTH) AND DATE_ADD( \''.$dateTo.'\', INTERVAL -1 MONTH),l.sum,0)) AS abondiff',
            'SUM(IF((MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11) AND b.bill_date BETWEEN  \''.$dateFrom.'\' AND  \''.$dateTo.'\',l.sum,0))-SUM(IF((MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11) AND b.bill_date BETWEEN DATE_ADD( \''.$dateFrom.'\', INTERVAL -1 MONTH) AND DATE_ADD( \''.$dateTo.'\', INTERVAL -1 MONTH),l.sum,0)) As overdiff',
            'b.bill_date',
        ]);

        $query->join('INNER JOIN', 'newbills b', 'c.id=b.client_id');
        $query->join('INNER JOIN', 'newbill_lines l', 'l.bill_no=b.bill_no');

        $query->andWhere('l.type = "service"');
        $query->andWhere(['not in', 'l.service', ['1C', 'bill_monthlyadd', '', 'all4net']]);

        $query->andWhere('b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND  :date_to', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);

        $query->groupBy(['l.service','c.id',]);
    }

    public function queryOrderBy()
    {
        return 'over DESC';
    }


}