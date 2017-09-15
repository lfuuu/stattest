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
use app\modules\nnp\column\NdcTypeColumn;
use app\widgets\GridViewExport\GridViewExport;
use yii\widgets\Breadcrumbs;

if (!isset(Yii::$app->request->get()['_pjax'])) {
    echo Breadcrumbs::widget([
        'links' => [
            ['label' => 'Телефония'],
            ['label' => $this->title = 'Отчет по данным calls_raw'],
        ],
    ]);

    $filter = require '_indexFilters.php';
}

$aggrDigitCount = [
    'sale_sum' => 4,
    'sale_avg' => 4,
    'sale_min' => 4,
    'sale_max' => 4,
    'cost_price_sum' => 4,
    'cost_price_avg' => 4,
    'cost_price_min' => 4,
    'cost_price_max' => 4,
    'margin_sum' => 4,
    'margin_avg' => 4,
    'margin_min' => 4,
    'margin_max' => 4,
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
            $attr = $filterModel->getGroupKeyParts($value)[0];
            $column = [
                'label' => $filterModel->getAttributeLabel($value),
                'attribute' => $attr,
            ];

            if (in_array($attr, ['sale', 'cost_price', 'orig_rate', 'term_rate'])) {
                $column['value'] = function ($model) use ($attr, $filterModel) {
                    return $model[$attr] / $filterModel->currency_rate;
                };
                $column['format'] = ['decimal', 4];
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
            'label' => $filterModel->getAttributeLabel($value),
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

$chooseError = function () use ($filterModel) {
    !$filterModel->isNnpFiltersPossible()
    && $error = 'В одном из списков ННП выбраны противоречивые значения';

    !$filterModel->isFilteringPossible()
    && $error = 'Выберите время начала разговора и хотя бы еще одно поле';

    !isset($error)
    && $error = Yii::t('yii', 'No results found.');

    return $error;
};

$dataProvider = $filterModel->getReport();

try {
    GridView::separateWidget([
        'isHideFilters' => false,
        'dataProvider' => $dataProvider,
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
        'emptyText' => isset($emptyText) ? $emptyText : $chooseError(),
        'exportWidget' => GridViewExport::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $filterModel,
            'columns' => $columns,
        ]),
        'panelHeadingTemplate' => '<div class="pull-right">
                                        {extraButtons}
                                    </div>
                                    <div class="pull-left">
                                        {summary}
                                    </div>
                                    <h3 class="panel-title">
                                        {heading}
                                    </h3>
                                    <div class="clearfix"></div>'
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