<?php
/**
 * Звонки в транке. Список звонков
 *
 * @var app\classes\BaseView $this
 * @var CallsRawFilter $filterModel
 */

use yii\data\ActiveDataProvider;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\billing\CallsRaw;
use app\models\DeferredTask;
use app\models\filter\CallsRawFilter;
use app\widgets\GridViewExport\GridViewExport;
use yii\db\ActiveQuery;
use yii\grid\ActionColumn;
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;

?>

<?= app\classes\Html::formLabel($this->title = 'Звонки в транке') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Межоператорка (отчеты)'],
        ['label' => $this->title, 'url' => '/voipreport/calls/trunc/'],
    ],
]);
$filterModelPath = CallsRawFilter::class;

?>


<?php Pjax::begin(['id' => 'deferred-task-table', 'timeout' => false, 'enablePushState' => false]) ?>
<?php
echo GridView::widget([
    'dataProvider' => new ActiveDataProvider(['query' =>
        DeferredTask::find()
            ->where(['!=', 'status', DeferredTask::STATUS_IN_REMOVING])
            ->andWhere(['filter_model' => $filterModelPath])
            ->orderBy(['created_at' => SORT_ASC])
    ]),
    'options' => ['id' => 'deferred-task-table'],
    'isFilterButton' => false,
    'columns' => [
        [
            'attribute' => 'userModel.name',
            'label' => 'Пользователь',
            'width' => '20%'

        ],
        [
            'attribute' => 'created_at',
            'width' => '10%'
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

<?php

$afterHeader = [];

// при скачивании не считать total
if (!\Yii::$app->request->get('action')) {

// отображаемые колонки Итого в гриде
    $dataProviderSummary = $filterModel->searchCostSummary();
    /** @var ActiveQuery $query */
    $query = $dataProviderSummary->query;
    /** @var CallsRaw $summary */
    $summary = $query->one();
    $summaryColumns = [
        [
            'content' => Yii::t('common', 'Summary'),
            'options' => ['colspan' => 9],
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'content' => sprintf('%.2f', $summary->billed_time_sum),
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => sprintf('%.2f', $summary->cost_sum),
        ],
        [
            'content' => sprintf('%.2f', $summary->interconnect_cost_sum),
        ],
        [
            'content' => sprintf('%.2f', $summary->cost_with_interconnect_sum),
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
    ];

    $afterHeader = [ // итого
        [
            'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING], // желтый фон
            'columns' => $summaryColumns,
        ]
    ];
}

$filterColumns = [
    [
        'attribute' => 'is_full_report',
        'class' => YesNoColumn::class,
    ],
];

if (!$filterModel->is_full_report) {
    $summaryColumns = [
        [
            'content' => Yii::t('common', 'Summary'),
        ],
        [
            'content' => sprintf('%.2f', $summary->billed_time_sum),
        ],
        [
            'content' => sprintf('%.2f', $summary->cost_sum),
        ],
        [
            'content' => sprintf('%.2f', $summary->interconnect_cost_sum),
        ],
        [
            'content' => sprintf('%.2f', $summary->cost_with_interconnect_sum),
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
    ];
    $afterHeader = [ // итого
        [
            'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING], // желтый фон
            'columns' => $summaryColumns,
        ]
    ];
}



?>

<form action="voipreport/deferred-task/new" id="calls-report-form">
<?php
$columns = $filterModel->getColumns();
$dataProvider = $filterModel->search();
$dataProvider->setTotalCount($summary->calls_count);

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
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
        'columns' => $columns,
        'resizableColumns' => false, // все равно не влезает на экран
        'emptyText' => $filterModel->isFilteringPossible() ? Yii::t('yii', 'No results found.') : 'Выберите транк и время начала разговора',
        'afterHeader' => $afterHeader,
        'exportWidget' => GridViewExport::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $filterModel,
            'columns' => $columns,
        ]),
        'beforeHeader' => [ // фильтры вне грида
            'columns' => $filterColumns,
        ],

    ]); ?>

</form>

