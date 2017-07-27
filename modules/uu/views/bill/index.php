<?php
/**
 * Список счетов
 *
 * @var \app\classes\BaseView $this
 * @var BillFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\MonthColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\uu\filter\BillFilter;
use app\modules\uu\models\Bill;
use app\widgets\GridViewExport\GridViewExport;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Bills'), 'url' => '/uu/bill']
    ],
]) ?>

<?php
$columns = [
    [
        'attribute' => 'id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'is_converted',
        'class' => YesNoColumn::className(),
    ],
    [
        'attribute' => 'date',
        'class' => MonthColumn::className(),
        'value' => function (Bill $bill) {
            return datefmt_format_object(
                new DateTime($bill->date),
                'LLL Y',
                Yii::$app->formatter->locale
            ); // нативный php date не поддерживает LLL/LLLL
        }
    ],
    [
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (Bill $bill) {
            return $bill->clientAccount->getLink();
        }
    ],
    [
        'attribute' => 'price',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'label' => 'Проводки, ¤',
        'format' => 'raw',
        'value' => function (Bill $bill) {
            return Html::a(
                    'Все',
                    Url::to(['/uu/account-entry/', 'AccountEntryFilter[bill_id]' => $bill->id])
                ) .
                '. ' .
                Html::a(
                    'Ненулевые',
                    Url::to(['/uu/account-entry/', 'AccountEntryFilter[bill_id]' => $bill->id, 'AccountEntryFilter[price_from]' => 0.01])
                ) .
                '.';
        },
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' =>
        $this->render('/invoice/_ico', ['clientAccountId' => $filterModel->client_account_id]) . ' ' .
        $this->render('/balance/_ico', ['clientAccountId' => $filterModel->client_account_id]),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
