<?php
/**
 * Main page view for Raw report (/voip/raw)
 *
 * @var CallsRawFilter $filterModel
 * @var \app\classes\BaseView $this
 * @var boolean $isNewVersion
 * @var boolean $isPreFetched
 */

use app\classes\grid\GridView;
use app\models\voip\filter\CallsRawFilter;
use app\classes\grid\column\universal\CheckboxColumn;
use app\widgets\GridViewExport\GridViewExport;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\classes\Html;
use yii\data\ActiveDataProvider;
use app\models\DeferredTask;
use yii\grid\ActionColumn;
use yii\widgets\Pjax;

// Если вызывающий контроллер не поддерживает кеширование
!isset($isPreFetched) && $isPreFetched = false;
!isset($isNewVersion) && $isNewVersion = false;
$filterModelPath = CallsRawFilter::class;

echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония'],
        ['label' => $this->title = 'Отчет по данным calls_raw'],
    ],
]);

$filters = require '_indexFilters.php';
// Если требуется поддержка кеша, то дополнитить выводимые колонки
if ($isPreFetched) {
    $filters[] = [
        'attribute' => 'calls_with_duration',
        'class' => CheckboxColumn::class,
    ];
}

$columns = $filterModel->getColumns($isCache);

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

// highlight filters with error
if ($filterModel->hasErrors()) {
    $required =
        $filterModel->hasRequiredFields()
            ? $filterModel->getRequiredValues()
            : []
    ;
    foreach ($filters as &$filter) {
        if (empty($filter['attribute'])) {
            continue;
        }

        $attribute = $filter['attribute'];
        if ($filterModel->hasErrors($attribute)) {
            $filter['filterOptions']['class'] = 'alert-danger';
        } elseif (array_key_exists($attribute, $required)) {
            $filter['filterOptions']['class'] = 'alert-warning';
        }
    }
}

try {
    echo '<form id="calls-report-form">';
    $dataProvider = $filterModel->getReport(true, $isNewVersion, $isPreFetched);
    GridView::separateWidget([
        'isHideFilters' => false,
        'dataProvider' => $dataProvider,
        'extraButtons' => $this->render('//layouts/_button', [
            'params' => [
                'class' => 'btn btn-warning',
                'onclick' => "$.ajax({
                type: 'get',
                url:  'voipreport/deferred-task/new',
                data: $('#calls-report-form').serialize() + '&filter_model=' + '" . addslashes($filterModelPath) . "',
                success: function(responseData, textStatus, jqXHR) {
                    alert('Отчет поставлен на отложенное формирование');
                    $.pjax.reload({container:\"#deferred-task-table\"});
                },
                error: function(responseData, textStatus, jqXHR) {
                    alert(responseData.responseText);
                },
            });"
            ],
            'text' => 'В отложенные задания',
            'glyphicon' => 'glyphicon-share-alt',
        ]),
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
        'panelHeadingTemplate' => $isNewVersion ?
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
                            function updateReportFilters(isFromCache) {
                                $("#isCacheCheckbox").prop("checked", isFromCache);
                                
                                var inputs = [
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
                                $url.searchParams.set("isCache", $(this).is(":checked") ? "1" : "0");
                                window.location.href = $url.href;
                            });
                        });
                    </script>
                </div>
            ' : '',
    ]);
    echo '</form>';
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

?>
<?php Pjax::begin(['id' => 'deferred-task-table', 'timeout' => false, 'enablePushState' => false]) ?>
<?php
echo \yii\grid\GridView::widget([
    'dataProvider' => new ActiveDataProvider(['query' =>
        DeferredTask::find()
            ->where(['!=', 'status', DeferredTask::STATUS_IN_REMOVING])
            ->andWhere(['filter_model' => $filterModelPath])
            ->orderBy(['created_at' => SORT_ASC])
    ]),
    'options' => ['id' => 'deferred-task-table'],
    'columns' => [
        [
            'attribute' => 'userModel.name',
            'label' => 'Пользователь',

        ],
        [
            'attribute' => 'created_at',
        ],
        [
            'attribute' => 'params',
            'format' => 'raw',
            'value' => function ($data) use ($filterModel) {
                $str = '';
                if (!($params = json_decode($data->params, true))) {
                    return 'Ошибка получения данных запроса';
                }
                $parsedParams = DeferredTask::parseBunch($params);

                $firstElements = '';
                $otherElements = '<div class="other-elements" style="display: none">';
                $callsRawFilterInstance = CallsRawFilter::instance();
                foreach($parsedParams as $key => $value) {
                    $current = '';
                    $trimmedVal = trim($value);
                    $label = $callsRawFilterInstance->getAttributeLabel($key);
                    if ($trimmedVal && $trimmedVal != '----') {
                        $current .= ($label) ? $label : $key;
                        $current .= ':' . $trimmedVal . '<br>';
                    }
                    if ($key == 'trunk' || $key == 'connect_time_from' ||  $key == 'connect_time_to') {
                        $firstElements .= $current;
                    } else {
                        $otherElements .= $current;
                    }
                };
                $str .= $firstElements;
                $str .= Html::tag('u', ' Развернуть ', ['style' => "cursor: pointer;", 'onclick' => "$($(this).siblings('.other-elements')).toggle();"]);
                $str .= $otherElements . '</div>';
                return $str;
            }
        ],
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function ($data) {
                $message = DeferredTask::getStatusLabels()[$data->status];
                if ($data->status == DeferredTask::STATUS_IN_PROGRESS) {
                    $message .= '<br>' . $data->getProgressString();
                } elseif ($data->status == DeferredTask::STATUS_READY) {
                    $message .= Html::a('', ['/voipreport/deferred-task/download', 'id' => $data->id], ['class' => 'glyphicon glyphicon-save', 'data-pjax' => 0]);
                }
                if ($data->status_text) {
                    $message .= '<br>' . $data->status_text;
                }
                return $message;
            },
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model, $key) {
                    return Html::tag('span', '',
                        ['class' => 'glyphicon glyphicon-trash pointer', 'onClick' => "
                            if (!confirm('Вы уверены, что хотите удалить запись?')) {
                                return false;
                            };
                            $.ajax({
                                type: 'get',
                                url:  'voipreport/deferred-task/remove',
                                data: {id: $model->id},
                                success: function(responseData, textStatus, jqXHR) {
                                    $.pjax.reload({container:\"#deferred-task-table\"});
                                },
                                error: function(responseData, textStatus, jqXHR) {
                                    alert(responseData.responseText);
                                }
                            });
                        "]);
                },
            ],
            'visibleButtons' => [
                'delete' => function ($model) {
                    return $model->status != DeferredTask::STATUS_IN_PROGRESS;
                },
            ]
        ],

    ],
]);
?>

<?php Pjax::end() ?>
