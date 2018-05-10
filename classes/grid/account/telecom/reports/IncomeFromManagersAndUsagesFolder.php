<?php
namespace app\classes\grid\account\telecom\reports;

use app\classes\grid\account\AccountGridFolder;
use app\classes\grid\account\AccountGridFolderSummaryTrait;
use app\classes\grid\column\billing\PayedColumn;
use app\classes\Html;
use app\models\ClientAccount;
use Yii;
use yii\db\Query;


/**
 * Class IncomeFromManagersAndUsagesFolder
 */
class IncomeFromManagersAndUsagesFolder extends AccountGridFolder
{
    // Суммы по колонкам abon, over, total из основной выборки
    use AccountGridFolderSummaryTrait;

    public $is_payed = '';

    /**
     * @return string
     */
    public function getName()
    {
        return 'Выручка по менеджеру и услугам';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            'contragent',
            'account_manager',
            'legal_entity',
            'currency',
            'service',
            'is_payed',
            'abon',
            'over',
            'total',
            'margin',
            'bill_date',
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultColumns()
    {
        return array_merge(parent::getDefaultColumns(), [
            'contragent' => [
                'attribute' => 'contragent',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/client/view?id=' . $data['id'] . '">' . $data['contragent'] . '</a>';
                },
                'filter' => function () {
                    return Html::textInput('companyName', Yii::$app->request->get('companyName'), [
                        'id' => 'searchByCompany',
                        'class' => 'form-control',
                    ]);
                },
            ],
            'is_payed' => [
                'attribute' => 'is_payed',
                'class' => PayedColumn::className(),
                'filterInputOptions' => [
                    'name' => 'is_payed'
                ],
            ],
            'margin' => [
                'attribute' => 'margin',
            ],
        ]);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'contragent' => 'Контрагент',
            'is_payed' => 'Статус оплаты',
            'margin' => 'Маржа',
        ]);
    }

    /**
     * @param Query $query
     */
    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        // Подготовка массива выбираемых полей
        $query->select = array_merge($query->select, [
            "concat(c.id, '( ', cg.name, ' )') contragent",
            'b.is_payed',
            'l.service',
        ], $this->getQuerySummarySelect(), ['b.bill_date']);

        $query->join('INNER JOIN', 'newbills b', 'c.id=b.client_id and biller_version = ' . ClientAccount::VERSION_BILLER_USAGE);
        $query->join('INNER JOIN', 'newbill_lines l', 'l.bill_no=b.bill_no');

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
        $query->groupBy([
            'cg.name',
            'cr.account_manager',
            'l.service',
            'b.is_payed',
            'b.bill_date',
            'cr.organization_id',
            'c.currency'
        ]);
    }

    /**
     * @return string
     */
    public function queryOrderBy()
    {
        return 'over DESC';
    }

    /**
     * @return \yii\data\ActiveDataProvider
     */
    public function spawnDataProvider()
    {
        $dataProvider = parent::spawnDataProvider();
        $query = $dataProvider->query;

        if ($this->is_payed !== '') {
            $query->andFilterWhere(['b.is_payed' => $this->is_payed]);
        }

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function getQuerySummarySelect()
    {
        return [
            'SUM(IF(MONTH(l.date_from) - MONTH(b.bill_date) = 0, l.sum, 0)) AS abon',
            'SUM(IF(MONTH(l.date_from) - MONTH(b.bill_date) = -1 OR MONTH(l.date_from) - MONTH(b.bill_date) = 11, l.sum, 0)) AS `over`',
            'SUM(l.sum) AS total',
            'ROUND(SUM((CASE WHEN l.cost_price > 0 THEN l.sum_without_tax - l.cost_price else 0 end )), 2) margin'
        ];
    }

    /**
     * @return int
     */
    public function getColspan()
    {
        return 6;
    }
}