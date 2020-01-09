<?php
/**
 * Вознаграждения партнеров
 *
 * @var \app\classes\BaseView $this
 * @var BillingApiFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\DropdownColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\billing\api\ApiMethod;
use app\models\billing\api\ApiRaw;
use app\models\filter\BillingApiFilter;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

echo Html::formLabel($this->title = 'Статистика: Вызовы-API');

echo Breadcrumbs::widget([
    'links' => [
        'Статистика',
        ['label' => $this->title, 'url' => $baseUrl = Url::toRoute('stats/billing-api')]
    ],
]);

if (!$filterModel->accountId) {
    echo Html::tag('div', 'Клиент не выбран', ['class' => 'alert alert-danger']);
    return;
}

if (!$filterModel->isLoad) {
    ?>

    <div class="row">
        <div class="col-sm-6 text-left">
            <div class="well">Итоговое потребление: <?=round($filterModel->getTotal(), 4)?></div>
        </div>
    </div>

    <?php
}
$form = ActiveForm::begin(['method' => 'get', 'action' => $baseUrl]);

$columns = [
    [
        'attribute' => 'connect_time',
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'api_method_id',
        'class' => DropdownColumn::class,
        'filter' => ApiMethod::getList(true),
        'value' => function (ApiRaw $row) {
            return $row->method->name;
        }
    ],
    [
        'attribute' => 'api_weight',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'rate',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'cost',
        'class' => IntegerRangeColumn::class,
        'value' => function (ApiRaw $row) {
            return -$row->cost;
        }
    ]

];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);

ActiveForm::end(); ?>