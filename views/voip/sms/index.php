<?php

use app\classes\grid\GridView;
use app\helpers\DateTimeZoneHelper;
use app\models\filter\SmsFilter;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var SmsFilter $searchModel */

$fromDate = $searchModel->from_datetime;
$toDate = $searchModel->to_datetime;

$urlData = [
    '/voip/sms',
    'SmsFilter' => [
        'group_by' => $searchModel->group_by,
        'number' => $searchModel->number,
        'from_datetime' => date('Y-m-01', time()),
        'to_datetime' => date('Y-m-t', time())
    ]];

$currentMonthUrl = $urlData;

$prevMonthDateTime = new DateTime('first day of previous month');
$urlData['SmsFilter']['from_datetime'] = $prevMonthDateTime->format('Y-m-01');
$urlData['SmsFilter']['to_datetime'] = $prevMonthDateTime->format('Y-m-t');

$previousMonthUrl = $urlData;

?>

<form>
    <div class="row">
        <div class="col-xs-2">
            Дата от
            <?php echo DatePicker::widget([
                'name' => 'SmsFilter[from_datetime]',
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
                'name' => 'SmsFilter[to_datetime]',
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
                'name' => 'SmsFilter[group_by]',
                'data' => [
                    'year' => 'Году',
                    'month' => 'Месяцу',
                    'day' => 'Дню',
                    'hour' => 'Часу',
                    'cost' => 'Цене',
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
        <div class="col-xs-2">
            Номер:
            <?= Select2::widget([
                'name' => 'SmsFilter[number]',
                'data' => \app\modules\uu\models\AccountTariff::find()
                    ->distinct()
                    ->select('voip_number')
                    ->where([
                        'service_type_id' => \app\modules\uu\models\ServiceType::ID_VOIP,
                        'client_account_id' => $searchModel->account_id,
                    ])
                    ->andWhere(['NOT', ['voip_number' => null]])
                    ->indexBy('voip_number')
                    ->orderBy(['voip_number' => SORT_ASC])
                    ->column(),
                'value' => $searchModel->number,
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

$columns = [$searchModel->group_by == 'cost' ? 'cost_gr' : 'setup_time'];
if (!$searchModel->group_by) {
    $columns = array_merge($columns, ['src_number', 'dst_number', 'rate']);
}

$columns = array_merge($columns, ['cost', 'count', 'parts']);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    'isFilterButton' => false,
]);

?>

<div>Общая стоимость: <?= $searchModel->allData['cost'] ?></div>
<div>Общее количество SMS: <?= $searchModel->allData['count'] ?></div>
<div>Общее количество частей: <?= $searchModel->allData['parts'] ?></div>
