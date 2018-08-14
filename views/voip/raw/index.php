<?php
/**
 * Main page view for Raw report (/voip/raw)
 *
 * @var CallsRawFilter $filterModel
 * @var \app\classes\BaseView $this
 * @var boolean $isSupport
 * @var boolean $isCache
 */

use app\classes\DateTimeWithUserTimezone;
use app\classes\grid\GridView;
use app\models\voip\filter\CallsRawFilter;
use app\classes\grid\column\universal\CheckboxColumn;
use app\modules\nnp\column\NdcTypeColumn;
use app\widgets\GridViewExport\GridViewExport;
use yii\widgets\Breadcrumbs;

// Если вызывающий контроллер не поддерживает кеширование
if (!isset($isCache)) {
    $isCache = false;
}
if (!isset($isSupport)) {
    $isSupport = false;
}

if (!isset(Yii::$app->request->get()['_pjax'])) {
    echo Breadcrumbs::widget([
        'links' => [
            ['label' => 'Телефония'],
            ['label' => $this->title = 'Отчет по данным calls_raw'],
        ],
    ]);

    $filter = require '_indexFilters.php';
    // Если поддержка кеша не требуется, то дополнитить выводимые колонки
    if ($isCache) {
        $filter[] = [
            'attribute' => 'calls_with_duration',
            'class' => CheckboxColumn::className(),
        ];
    }
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
    // Если поддержка кеша не требуется, то дополнитить выводимые колонки
    if (!$isCache) {
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
    !$filterModel->isNnpFiltersPossible()
    && $error = 'В одном из списков ННП выбраны противоречивые значения';

    !$filterModel->isFilteringPossible()
    && $error = 'Выберите время начала разговора и хотя бы еще одно поле';

    !isset($error)
    && $error = Yii::t('yii', 'No results found.');

    return $error;
};

$dataProvider = $filterModel->getReport(true, $isSupport, $isCache);

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
        'panelHeadingTemplate' => $isSupport ?
            '
                <div class="row">
                    <div class="col-md-12">
                        <h2>В режиме кэширования будут недоступны некоторые фильтры</h2>
                    </div>
                    <div class="col-md-12">
                        <input type="checkbox" value="1" id="isCacheCheckbox">
                        <label for="isCacheCheckbox">Использовать кэш</label>
                    </div>
                    <script>
                        $(document).ready(function(){
                            let $url = new URL(window.location.href);
                            if (parseInt($url.searchParams.get("isCache")) === 1) {
                                $("#isCacheCheckbox").prop("checked", true);
                                $("#callsrawfilter-src_number").prop("disabled", true);
                                $("#callsrawfilter-dst_number").prop("disabled", true);
                                $("#callsrawfilter-src_destinations_ids").prop("disabled", true);
                                $("#callsrawfilter-dst_destinations_ids").prop("disabled", true)
                                $("#callsrawfilter-session_time_from").prop("disabled", true);
                                $("#callsrawfilter-session_time_to").prop("disabled", true);
                            }
                            $("#isCacheCheckbox").on("click", function () {
                                if ($(this).is(":not(:checked)")) {
                                    $url.searchParams.set("isCache", "0");
                                    $("#callsrawfilter-src_number").prop("disabled", false);
                                    $("#callsrawfilter-dst_number").prop("disabled", false);
                                    $("#callsrawfilter-src_destinations_ids").prop("disabled", false);
                                    $("#callsrawfilter-dst_destinations_ids").prop("disabled", false)
                                    $("#callsrawfilter-session_time_from").prop("disabled", false);
                                    $("#callsrawfilter-session_time_to").prop("disabled", false);
                                } else {
                                    $url.searchParams.set("isCache", "1");
                                    $("#callsrawfilter-src_number").prop("disabled", true);
                                    $("#callsrawfilter-dst_number").prop("disabled", true);
                                    $("#callsrawfilter-src_destinations_ids").prop("disabled", true);
                                    $("#callsrawfilter-dst_destinations_ids").prop("disabled", true)
                                    $("#callsrawfilter-session_time_from").prop("disabled", true);
                                    $("#callsrawfilter-session_time_to").prop("disabled", true);
                                }
                                window.location.href = $url.href;
                            });
                        });
                    </script>
                </div>
            ' : '',
    ]);
} catch (yii\db\Exception $e) {
    Yii::$app->session->addFlash(
        'error',
        ($e->getCode() == 8) ?
            'Запрос слишком тяжелый, чтобы выполниться. Задайте, пожалуйста, другие фильтры' :
            'Ошибка выполнения запроса: ' . $e->getMessage()
    );
}
