<?php
namespace app\classes\grid\account\telecom\reports;

use app\classes\grid\account\AccountGridFolder;
use app\models\ClientAccount;
use Yii;
use yii\db\Query;


/**
 * Class IncomeFromUsagesFolder
 */
class IncomeFromUsagesFolder extends AccountGridFolder
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Доход по услугам';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            'service',
            'region',
            'legal_entity',
            'abon',
            'over',
            'total',
            'bill_date',
        ];
    }

    /**
     * @param Query $query
     */
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

        $query->join('INNER JOIN', 'newbills b', 'c.id=b.client_id and biller_version = ' . ClientAccount::VERSION_BILLER_UNIVERSAL);
        $query->join('INNER JOIN', 'newbill_lines l', 'l.bill_no=b.bill_no');

        $query->andWhere('b.is_payed = 1');
        $query->andWhere('l.type = "service"');
        $query->andWhere(['not in', 'l.service', ['1C', 'bill_monthlyadd', '', 'all4net']]);

        list($dateFrom, $dateTo) = preg_split('/[\s+]\-[\s+]/', $this->bill_date);
        if (!$dateFrom) {
            $dateFrom = date('Y-m-01');
        }

        if (!$dateTo) {
            $dateTo = date('Y-m-t');
        }

        $query->andWhere('b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND  :date_to', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);

        // $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->groupBy(['l.service']);
    }

    /**
     * @return string
     */
    public function queryOrderBy()
    {
        return 'over DESC';
    }


}