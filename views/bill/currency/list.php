<?php
/**
 * Список курсов валют
 *
 * @var \yii\web\View $this
 * @var CurrencyRateFilter $filterModel
 */

use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\DateRangeColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\models\filter\CurrencyRateFilter;
use kartik\grid\GridView;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title = Yii::t('currency', 'Currency rate'), 'url' => '/bill/currency/index/'],
    ],
]) ?>

<?php
$columns = [
    array(
        'attribute' => 'date',
        'class' => DateRangeColumn::className(),
    ),
    array(
        'attribute' => 'rate',
        'class' => IntegerRangeColumn::className(),
    ),
    [
        'attribute' => 'currency',
        'class' => CurrencyColumn::className(),
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);