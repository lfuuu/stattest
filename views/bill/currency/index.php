<?php
/**
 * Список курсов валют
 *
 * @var app\classes\BaseView $this
 * @var CurrencyRateFilter $filterModel
 */

use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\GridView;
use app\models\filter\CurrencyRateFilter;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Курс валюты') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Бухгалтерия'],
        ['label' => $this->title, 'url' => '/bill/currency/index/'],
    ],
]) ?>

<?php
$columns = [
    [
        'attribute' => 'date',
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'rate',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'currency',
        'class' => CurrencyColumn::class,
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);