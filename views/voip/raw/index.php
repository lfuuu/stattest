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
use app\classes\grid\column\universal\CheckboxColumn;
use app\modules\nnp\column\NdcTypeColumn;
use app\widgets\GridViewExport\GridViewExport;
use yii\widgets\Breadcrumbs;

echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония'],
        ['label' => $this->title = 'Отчет по данным calls_raw'],
    ],
]);

$filters = require '_indexFilters.php';
// Если требуется предрасчет, то дополнить выводимые колонки
if ($filterModel->isPreFetched) {
    $filters[] = [
        'attribute' => 'calls_with_duration',
        'class' => CheckboxColumn::class,
    ];
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
    'margin_percent' => 2,
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
                $column['class'] = NdcTypeColumn::class;
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
                return DateTimeWithUserTimezone::formatSecondsToDetailedView($model[$value]);
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
    // Если предрасчет не требуется, то дополним выводимые колонки
    if (!$filterModel->isPreFetched) {
        $columns[] = [
            'label' => 'Номер А',
            'attribute' => 'src_number',
        ];
        $columns[] = [
            'label' => 'Номер В',
            'attribute' => 'dst_number',
        ];
        $columns[] = [
            'label' => 'ПДД',
            'attribute' => 'pdd',
        ];
    }
}

$chooseError = function () use ($filterModel) {
    $errors = [];
    foreach ($filterModel->getErrors() as $key => $error) {
        $errorText = 'Ошибка: ' . $error[0];
        $errors[] = $errorText;
        Yii::$app->session->addFlash('error', $errorText);
    }

    if (empty($errors)) {
        $errors[] = Yii::t('yii', 'No results found.');
    }

    return implode('<br />', $errors);
};

/**
 * @param CallsRawFilter $model
 */
$highLightErrors = function (CallsRawFilter $model) use(&$filters) {
    if ($model->hasErrors()) {
        $required =
            $model->hasRequiredFields()
                ? $model->getRequiredValues()
                : [];

        foreach ($filters as &$filter) {
            if (empty($filter['attribute'])) {
                continue;
            }

            $attribute = $filter['attribute'];
            $attributes = [$attribute];
            if ($attribute == 'connect_time') {
                $attributes = ['connect_time_from', 'connect_time_to'];
            }
            foreach ($attributes as $attribute) {
                if ($model->hasErrors($attribute)) {
                    $filter['filterOptions']['class'] = 'alert-danger';
                } elseif (array_key_exists($attribute, $required)) {
                    $filter['filterOptions']['class'] = 'alert-warning';
                }
            }
        }
    }
};

/**
 * @param CallsRawFilter $model
 * @return string
 */
$getHeader = function (CallsRawFilter $model) {
    if ($model->isByPeerId) {
        return '
<div class="row">
    <div class="col-md-12">
        <h2>Вы используете устаревшую логику склейки - данные могут отображатся лишь частично (около 50% от общего количества).</h2>
    </div>
</div>
';
    }

    if ($model->isFromUnite) {
        return '
<div class="row">
    <div class="col-md-12">
        <h2>Отчет по маржинальности транзитного траффика по таблице склейки</h2>
    </div>
</div>
';
    }

    if ($model->isNewVersion) {
        return '
                <div class="row">
                    <div class="col-md-12">
                        <h2>В режиме предрасчёта будут недоступны некоторые фильтры</h2>
                    </div>
                    <div class="col-md-12">
                        <input type="checkbox" value="1" id="isCacheCheckbox">
                        <label for="isCacheCheckbox">Использовать предрасчёт</label>
                    </div>
                    <script>
                        $(document).ready(function(){
                            function updateReportFilters(isFromCache) {
                                $("#isCacheCheckbox").prop("checked", isFromCache);
                                
                                var inputs = [
                                    "#callsrawfilter-traffictype",
                                    "#callsrawfilter-src_number",
                                    "#callsrawfilter-dst_number",
                                    "#callsrawfilter-src_destinations_ids",
                                    "#callsrawfilter-dst_destinations_ids",
                                    "#callsrawfilter-session_time_from",
                                    "#callsrawfilter-session_time_to",
                                ];
                                for (var key in inputs) {
                                    $(inputs[key]).prop("disabled", true);
                                    $(inputs[key]).closest("div.alert-warning").removeClass("alert-warning");
                                    $(inputs[key]).closest("div.alert-danger").removeClass("alert-danger");
                                }
                            }
                            
                            var $url = new URL(window.location.href);
                            if (parseInt($url.searchParams.get("isCache")) === 1) {
                                updateReportFilters(true);
                            }
                            $("#isCacheCheckbox").on("click", function () {
                                if ($.pjax.state !== undefined && $.pjax.state.url !== undefined) {
                                    $url = new URL($.pjax.state.url);
                                }
                                $url.searchParams.set("isCache", $(this).is(":checked") ? "1" : "0");
                                window.location.href = $url.href;
                            });
                        });
                    </script>
                </div>
            ';
    }

    return '';
};

try {
    $highLightErrors($filterModel);

    $dataProvider = $filterModel->getReport();

    GridView::separateWidget([
        'isHideFilters' => false,
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'beforeHeader' => [
            'columns' => $filters
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
        'panelHeadingTemplate' => $getHeader($filterModel),
    ]);
} catch (yii\db\Exception $e) {
    Yii::$app->session->addFlash(
        'error',
        ($e->getCode() == 8) ?
            'Запрос слишком тяжелый, чтобы выполниться. Задайте, пожалуйста, другие фильтры' :
            'Ошибка выполнения запроса: ' . $e->getMessage()
    );
} catch (\Exception $e) {
    Yii::$app->session->addFlash(
        'error',
        'Неизвестная ошибка: ' . $e->getMessage()
    );
}
