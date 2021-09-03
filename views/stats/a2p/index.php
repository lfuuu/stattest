<?php

use app\classes\grid\GridView;
use app\helpers\DateTimeZoneHelper;
use app\models\filter\A2pFilter;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var A2pFilter $searchModel */

$fromDate = $searchModel->from_datetime;
$toDate = $searchModel->to_datetime;

$urlData = [
    '/stats/a2p',
    'A2pFilter' => [
        'group_by' => $searchModel->group_by,
        'number' => $searchModel->number,
        'from_datetime' => date('Y-m-01', time()),
        'to_datetime' => date('Y-m-t', time())
    ]];

$currentMonthUrl = $urlData;

$prevMonthDateTime = new DateTime('first day of previous month');
$urlData['A2pFilter']['from_datetime'] = $prevMonthDateTime->format('Y-m-01');
$urlData['A2pFilter']['to_datetime'] = $prevMonthDateTime->format('Y-m-t');

$previousMonthUrl = $urlData;

$breadCrumbLinks = [
    'Статистика',
    ['label' => 'SMS (A2P)', 'url' => '/stats/a2p'],
];

?>

<?= \yii\widgets\Breadcrumbs::widget(['links' => $breadCrumbLinks]) ?>

<form>
    <div class="row">
        <div class="col-xs-2">
            Дата от
            <?php echo DatePicker::widget([
                'name' => 'A2pFilter[from_datetime]',
                'type' => DatePicker::TYPE_INPUT,
                'value' => (new DateTime($fromDate))->format(DateTimeZoneHelper::DATE_FORMAT),
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ],
                'options' => [
                    'class' => 'process-datepicker',
                ],
            ]); ?>
        </div>

        <div class="col-xs-2">
            Дата до
            <?php echo DatePicker::widget([
                'name' => 'A2pFilter[to_datetime]',
                'type' => DatePicker::TYPE_INPUT,
                'value' => (new DateTime($toDate))->modify('-1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ],
                'options' => [
                    'class' => 'process-datepicker',
                ],
            ]); ?>
        </div>
        <div class="col-xs-3">
            Группировать по
            <?= Select2::widget([
                'name' => 'A2pFilter[group_by]',
                'data' => [
                    'year' => 'Году',
                    'month' => 'Месяцу',
                    'day' => 'Дню',
                    'hour' => 'Часу'
                ],
                'value' => $searchModel->group_by,
                'options' => [
                    'placeholder' => '-------'
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])
            ?>
        </div>
        <div class="col-xs-3">
            <input type="submit" value="Сформировать" style="margin-top: 20px" class="btn btn-primary">
        </div>
    </div>
</form>

<div><br>
    Показать статистику:
    <?= Html::a('За прошлый месяц', $previousMonthUrl); ?>&nbsp
    <?= Html::a('За этот месяц', $currentMonthUrl); ?>
</div>

<?php

$columns = ['charge_time'];
if (!$searchModel->group_by) {
    $columns = array_merge($columns, ['src_number', 'dst_number', 'dst_route', 'rate']);
}

$columns = array_merge($columns, ['cost', 'count']);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    'isFilterButton' => false,
]);

?>

<div>Общая стоимость: <?= $searchModel->allData['cost'] . ' ' . $searchModel->clientAccount->currencyModel->symbol($searchModel->clientAccount->currency) ?></div>
<div>Общее количество SMS: <?= $searchModel->allData['count'] ?></div>
<div>Общее количество частей: <?= $searchModel->allData['parts'] ?></div>
