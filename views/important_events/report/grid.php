<?php

use app\classes\grid\GridView;
use yii\data\ActiveDataProvider;
use app\classes\Html;
use app\models\important_events\ImportantEvents;
use app\helpers\DateTimeZoneHelper;
use app\classes\grid\column\important_events\ClientColumn;
use app\classes\grid\column\important_events\EventNameColumn;
use app\classes\grid\column\important_events\SourceColumn;
use app\classes\grid\column\important_events\IpColumn;
use app\classes\grid\column\universal\TagsColumn;

/** @var ActiveDataProvider $dataProvider */
/** @var ImportantEvents $filterModel */

echo Html::formLabel('Лог значимых событий');

foreach (\app\models\important_events\ImportantEventsNames::find()->all() as $event) {
    $eventsList[$event->group->title][$event->code] = $event->value;
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => 'kartik\grid\ExpandRowColumn',
            'width' => '50px',
            'value' => function ($model, $key, $index, $column) {
                return GridView::ROW_COLLAPSED;
            },
            'detail' => function ($model, $key, $index, $column) {
                return $this->render('details', ['model' => $model]);
            },
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        [
            'class' => ClientColumn::class,
            'width' => '25%',
        ],
        'date' => [
            'attribute' => 'date',
            'width' => '20%',
            'format' => 'raw',
            'filter' => \kartik\daterange\DateRangePicker::widget([
                'name' => $filterModel->formName() . '[date]',
                'presetDropdown' => true,
                'value' => $filterModel->date ?: (new DateTime)->format('Y-m-d') . ' - ' . (new DateTime)->format('Y-m-d'),
                'pluginOptions' => [
                    'locale' => [
                        'format' => 'YYYY-MM-DD',
                        'separator' => ' - ',
                    ],
                ],
                'options' => [
                    'class' => 'form-control input-sm',
                    'style' => 'font-size: 12px; height: 30px;',
                ],
            ]),
            'value' => function ($model, $key, $index, $column) {
                return
                    Yii::$app->formatter->asDateTime(
                        (new DateTime($model->date))
                            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::getUserTimeZone()))
                    );
            },
        ],
        [
            'class' => EventNameColumn::class,
            'width' => '20%',
        ],
        [
            'class' => SourceColumn::class,
            'width' => '10%',
        ],
        [
            'class' => IpColumn::class,
            'width' => '10%',
        ],
        [
            'class' => TagsColumn::class,
            'filter' => TagsColumn::class,
            'width' => '*',
        ]
    ],
]);
?>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('button[data-important-event-id]').on('click', function(e) {
        e.preventDefault();

        $.ajax({
            url: '/important_events/report/set-comment/',
            data: {
                'id': $(this).data('important-event-id'),
                'comment': $('input[data-important-event-id="' + $(this).data('important-event-id') + '"]').val()
            },
            method: 'POST'
        });
    });
})
</script>
