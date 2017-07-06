<?php
/**
 * Main page view for Raw report (/voip/raw)
 *
 * @var CallsRawFilter $filterModel
 * @var \app\classes\BaseView $this
 */

use app\classes\DateTimeWithUserTimezone;
use app\classes\grid\GridView;
use app\models\voip\filter\CallsRawFilter;
use yii\widgets\Breadcrumbs;
use app\modules\nnp\column\NdcTypeColumn;

if (!isset(Yii::$app->request->get()['_pjax'])) {
    echo app\classes\Html::formLabel($this->title = 'Отчет по данным calls_raw');
    echo Breadcrumbs::widget([
        'links' => [
            ['label' => 'Телефония'],
            ['label' => $this->title],
        ],
    ]);

    $filter = require '_indexFilters.php';
}

$aggrDigitCount = [
    'sale_sum' => 2,
    'sale_avg' => 2,
    'sale_min' => 2,
    'sale_max' => 2,
    'cost_price_sum' => 2,
    'cost_price_avg' => 2,
    'cost_price_min' => 2,
    'cost_price_max' => 2,
    'margin_sum' => 2,
    'margin_avg' => 2,
    'margin_min' => 2,
    'margin_max' => 2,
];

$columns = [];

if ($filterModel->group || $filterModel->group_period || $filterModel->aggr) {
    if ($filterModel->group_period) {
        $columns[] = [
            'label' => 'Интервал',
            'attribute' => 'interval',
        ];
    }

    if ($filterModel->group) {
        foreach ($filterModel->group as $key => $value) {
            $attr = $filterModel->getGroupKeyParts($value)[1];
            $column = [
                    'label' => $filterModel->groupConst[$value],
                    'attribute' => $attr,
            ];

            if ($attr == 'sale' || $attr == 'cost_price') {
                $column['value'] = function ($model) use ($attr, $filterModel) {
                    return $model[$attr] / $filterModel->currency_rate;
                };
                $column['format'] = ['decimal', 2];
            }

            if ($attr == 'src_ndc_type_id' || $attr == 'dst_ndc_type_id') {
                $column['class'] = NdcTypeColumn::className();
            }

            $columns[] = $column;
        }
    }

    $c = count($columns);
    foreach ($filterModel->aggr as $key => $value) {
        $columns[$key + $c] = [
            'label' => $filterModel->aggrLabels[$value],
            'attribute' => $value,
        ];
        if (strpos($value, 'session_time') !== false || strpos($value, 'acd') !== false) {
            $columns[$key + $c]['value'] = function ($model) use ($value) {
                return DateTimeWithUserTimezone::formatSecondsToMinutesAndSeconds($model[$value]);
            };
        }

        if ($filterModel->currency_rate !== 1 && (strpos($value, 'sale') !== false || strpos($value, 'cost_price') !== false || strpos($value, 'margin') !== false)) {
            $columns[$key + $c]['value'] = function ($model) use ($value, $filterModel) {
                return $model[$value] / $filterModel->currency_rate;
            };
        }

        if (isset($aggrDigitCount[$value])) {
            $columns[$key + $c]['format'] = ['decimal', $aggrDigitCount[$value]];
        }

        if (strpos($value, 'asr') !== false) {
            $columns[$key + $c]['format'] = 'percent';
        }
    }
} else {
    $columns = require '_indexColumns.php';
}

$report = $filterModel->getReport();

try {
    GridView::separateWidget([
        'dataProvider' => $report,
        'filterModel' => $filterModel,
        'beforeHeader' => [
            'columns' => $filter
        ],
        'pjaxSettings' => [
            'options' => [
                'timeout' => 180000,
                'enableReplaceState' => true,
            ]
        ],
        'columns' => $columns,
        'filterPosition' => '',
        'emptyText' => isset($emptyText) ?
            $emptyText :
            (
                $filterModel->isFilteringPossible() ?
                    Yii::t('yii', 'No results found.') :
                    'Выберите время начала разговора и хотя бы еще одно поле'
            ),
        'exportWidget' => \app\widgets\GridViewExport\GridViewExport::widget([
            'dataProvider' => $report,
            'filterModel' => $filterModel,
            'columns' => $columns,
            'batchSize' => 1000,
        ]),
    ]);
} catch (yii\db\Exception $e) {
    if ($e->getCode() == 8) {
        Yii::$app->session->addFlash(
            'error',
            'Запрос слишком тяжелый, чтобы выполниться. Задайте, пожалуйста, другие фильтры'
        );
    } else {
        Yii::$app->session->addFlash('error', 'Ошибка выполнения запроса: ' . $e->getMessage());
    }
}