<?php

use yii\data\ActiveDataProvider;
use kartik\grid\GridView;
use app\classes\Html;
use app\models\important_events\ImportantEvents;

/** @var $dataProvider ActiveDataProvider */
/** @var ImportantEvents $filterModel */

echo Html::formLabel('Логи оповeщений');

echo GridView::widget([
    'id' => 'LkNotifyLog',
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => 'app\classes\grid\column\important_events\ClientColumn',
            'width' => '15%',
        ],
        'date' => [
            'attribute' => 'date',
            'width' => '12%',
            'format' => 'raw',
            'filter' => \kartik\daterange\DateRangePicker::widget([
                'name' => $filterModel->formName() . '[date]',
                'presetDropdown' => false,
                'hideInput' => true,
                'value' => $filterModel->date ?: (new DateTime('first day of this month'))->format('Y-m-d') . ' - ' . (new DateTime('last day of this month'))->format('Y-m-d'),
                'pluginOptions' => [
                    'format' => 'YYYY-MM-DD',
                    'ranges' => [
                        'Текущий месяц' => ['moment().startOf("month")', 'moment().endOf("month")'],
                        'Прошлый месяц' => ['moment().subtract(1,"month").startOf("month")', 'moment().subtract(1,"month").endOf("month")'],
                        'Сегодня' => ['moment().startOf("day")', 'moment()'],
                    ],
                ],
                'containerOptions' => [
                    'style' => 'overflow: hidden;',
                    'class' => 'drp-container input-group',
                ]
            ])
        ],
        [
            'class' => 'app\classes\grid\column\important_events\EventNameColumn',
            'width' => '15%',
        ],
        [
            'class' => 'app\classes\grid\column\important_events\PropertiesColumn',
            'width' => '30%',
        ],
    ],
    'pjax' => false,
    'toolbar'=> [],
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'hover' => true,
    'panel'=>[
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
?>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('.select2').select2({
        templateResult: function(item) {
            console.log(item);
            var $result =
                $('<input />')
                    .attr('type', 'checkbox')
                    .attr('name', 'test')
                    .val(item.val());

            return $result + item.text;
        }
    });
});
</script>
