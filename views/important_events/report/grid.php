<?php

use app\classes\grid\column\important_events\ClientColumn;
use app\classes\grid\column\important_events\EventNameColumn;
use app\classes\grid\column\important_events\SourceColumn;
use app\classes\grid\column\universal\TagsColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\important_events\ImportantEventsDetailsFactory;
use app\helpers\DateTimeZoneHelper;
use app\models\important_events\ImportantEvents;
use kartik\daterange\DateRangePicker;
use kartik\grid\ExpandRowColumn;
use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

/** @var ActiveDataProvider $dataProvider */
/** @var ImportantEvents $filterModel */

echo Html::formLabel('Лог значимых событий');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Группы событий', 'url' => Url::toRoute(['/important_events/groups/'])],
        ['label' => 'Источники событий', 'url' => Url::toRoute(['/important_events/sources/'])],
        ['label' => 'Лог значимых событий', 'url' => Url::toRoute(['/important_events/report/'])],
    ],
]);

$ipMap = [
    '85.94.32.98' => 'МСН офис. Москва',
    '85.94.32.198' => 'МСН офис. Москва',
    '178.48.22.33' => 'МСН офис. Венгрия',
    '185.66.52.41' => 'МСН офис. Венгрия',
    '81.183.239.147' => 'МСН офис. Венгрия',
    '185.66.53.70' => 'МСН офис. Венгрия. (k8 sites)',
    '185.18.110.250' => 'МСН офис. Краснодар',
    '46.228.0.5' => 'МСН офис. Спб',
    '5.129.54.18' => 'МСН офис. Новосибирск'
];


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => ExpandRowColumn::class,
            'width' => '50px',
            'value' => function () {
                return GridView::ROW_COLLAPSED;
            },
            'detail' => function ($model) {
                return $this->render('details', ['model' => $model]);
            },
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        [
            'attribute' => 'client_id',
            'class' => ClientColumn::class,
            'width' => '25%',
        ],
        'date' => [
            'attribute' => 'date',
            'width' => '15%',
            'format' => 'raw',
            'filter' => DateRangePicker::widget([
                'name' => $filterModel->formName() . '[date]',
                'presetDropdown' => true,
                'value' => $filterModel->date ?:
                    (new DateTime)->format(DateTimeZoneHelper::DATE_FORMAT) .
                    ' - ' .
                    (new DateTime)->format(DateTimeZoneHelper::DATE_FORMAT),
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
            'value' => function ($model) {
                return ImportantEventsDetailsFactory::get($model->event, $model)->getProperty('date.formatted');
            },
        ],
        [
            'attribute' => 'event',
            'class' => EventNameColumn::class,
            'width' => '15%',
        ],
        [
            'class' => SourceColumn::class,
            'width' => '10%',
        ],
        [
            'attribute' => 'ip',
            'format' => 'raw',
            'value' => function ($model) {
                return ImportantEventsDetailsFactory::get($model->event, $model)->getProperty('ip');
            },
            'width' => '10%',
        ],
        [
            'attribute' => 'remote_ip',
            'format' => 'raw',
            'value' => function($model) use ($ipMap) {
                $xFwdIp =  ImportantEventsDetailsFactory::get($model->event, $model)->getProperty('HTTP_X_FORWARDED_FOR');
                $ip = $xFwdIp ? $xFwdIp : $model->remote_ip;

                return $ipMap[$ip] ?? $ip;
            },
            'width' => '10%',
        ],
        [
            'attribute' => 'login',
            'width' => '10%',
            'format' => 'raw',
            'value' => function ($model) {
                $suportEmail = ImportantEventsDetailsFactory::get($model->event, $model)->getProperty('support_email');
                return $suportEmail ? Html::tag('span', $suportEmail, ['class' => 'text-info']) : ImportantEventsDetailsFactory::get($model->event, $model)->getProperty('login_email');
            },
        ],
        [
            'class' => TagsColumn::class,
            'filter' => TagsColumn::class,
            'width' => '20%',
        ]
    ],
]);