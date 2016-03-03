<?php
/**
 * Список курсов валют
 *
 * @var \yii\web\View $this
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
        ['label' => $this->title, 'url' => '/bill/currency/index/'],
    ],
]) ?>

<?php
$columns = [
    [
        'attribute' => 'date',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'rate',
        'class' => IntegerRangeColumn::className(),
    ],
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