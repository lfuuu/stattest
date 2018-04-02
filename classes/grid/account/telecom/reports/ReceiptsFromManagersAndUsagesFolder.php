<?php

namespace app\classes\grid\account\telecom\reports;

use app\classes\grid\account\AccountGridFolder;
use app\classes\grid\account\AccountGridFolderSummaryTrait;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\User;
use app\modules\uu\column\ServiceTypeColumn;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountTariff;
use yii\db\Query;

class ReceiptsFromManagersAndUsagesFolder extends AccountGridFolder
{
    use AccountGridFolderSummaryTrait;

    public $service_type;

    /**
     * @return string
     */
    public function getName()
    {
        return 'Выручка по менеджеру и услугам УЛС';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            'company',
            'account_manager',
            'service_type',
            'sum_connection',
            'sum_abonent_pay',
            'sum_minimum_pay',
            'sum_resources',
            'sum_all',
            'bill_date'
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'service_type' => 'Услуга',
            'sum_connection' => 'Плата за подключение',
            'sum_abonent_pay' => 'Абонентская плата',
            'sum_minimum_pay' => 'МГП',
            'sum_resources' => 'Ресурсы',
            'sum_all' => 'Всего',
        ]);
    }

    /**
     * @param Query $query
     */
    public function queryParams(Query $query)
    {
        list($dateFrom, $dateTo) = preg_split('/[\s+]\-[\s+]/', $this->bill_date);

        $query
            ->select([
                'concat(cg.id, \'( \', cg.name, \' )\') company',
                'amu.name account_manager_name',
                'uat.service_type_id service_type',
                'sum(case u.type_id when -1 then u.price else 0 end ) sum_connection',
                'sum(case u.type_id when -2 then u.price else 0 end ) sum_abonent_pay',
                'sum(case u.type_id when -3 then u.price else 0 end ) sum_minimum_pay',
                'sum(case when u.type_id > 0 then u.price else 0 end ) sum_resources',
                'sum(u.price) sum_all',
                'b.bill_date'
            ])
            ->from([ClientAccount::tableName() . ' c'])
            ->innerJoin(ClientContract::tableName() . ' cr', 'c.contract_id = cr.id')
            ->innerJoin(ClientContragent::tableName() . ' cg', 'cr.contragent_id = cg.id')
            ->leftJoin(User::tableName() . ' amu', 'cr.account_manager = amu.user')
            ->innerJoin(Bill::tableName() . ' b', 'c.id = b.client_id')
            ->innerJoin(BillLine::tableName() . ' bl', 'b.bill_no = bl.bill_no')
            ->innerJoin(AccountEntry::tableName() . ' u', 'bl.uu_account_entry_id = u.id')
            ->innerJoin(AccountTariff::tableName() . ' uat', 'u.account_tariff_id = uat.id')
            ->where([
                'and',
                ['=', 'b.biller_version', ClientAccount::VERSION_BILLER_UNIVERSAL],
                ['=', 'b.is_payed', 1],
                ['=', 'bl.type', 'service'],
            ])
            ->andWhere(
                'b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND  :date_to',
                [
                    'date_from' => $dateFrom ?: date('Y-m-01'),
                    'date_to' => $dateTo ?: date('Y-m-t'),
                ]
            )
            ->groupBy([
                'cg.name',
                'cr.account_manager',
                'uat.service_type_id',
                'b.bill_date'
            ]);
    }

    /**
     * @return \yii\data\ActiveDataProvider
     */
    public function spawnDataProvider()
    {
        $dataProvider = parent::spawnDataProvider();
        $query = $dataProvider->query;

        if ($this->service_type) {
            $query->andFilterWhere(['uat.service_type_id' => $this->service_type]);
        }

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function getQuerySummarySelect()
    {
        return [
            'SUM(case u.type_id when -1 then u.price else 0 end ) sum_connection',
            'SUM(case u.type_id when -2 then u.price else 0 end ) sum_abonent_pay',
            'SUM(case u.type_id when -3 then u.price else 0 end ) sum_minimum_pay',
            'SUM(case when u.type_id > 0 then u.price else 0 end ) sum_resources',
            'SUM(u.price) sum_all',
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultColumns()
    {
        return array_merge(parent::getDefaultColumns(), [
            'service_type' => [
                'attribute' => 'service_type',
                'class' => ServiceTypeColumn::className(),
                'filterInputOptions' => [
                    'name' => 'service_type'
                ],
            ],
            'sum_connection' => [
                'attribute' => 'sum_connection',
            ],
            'sum_abonent_pay' => [
                'attribute' => 'sum_abonent_pay',
            ],
            'sum_minimum_pay' => [
                'attribute' => 'sum_minimum_pay',
            ],
            'sum_resources' => [
                'attribute' => 'sum_resources',
            ],
            'sum_all' => [
                'attribute' => 'sum_all',
            ],
        ]);
    }

    /**
     * @return string
     */
    public function queryOrderBy()
    {
        return 'cg.id DESC';
    }

    /**
     * @return int
     */
    public function getColspan()
    {
        return 3;
    }
}