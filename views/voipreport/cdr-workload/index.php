<?php
/**
 * Main page view for number cdr-workload report (/voipreport/cdr-workload)
 *
 * @var CdrWorkload $filterModel
 * @var \yii\web\View $this
 */

use app\classes\grid\GridView;
use app\models\voip\filter\CdrWorkload;
use yii\widgets\Breadcrumbs;
use app\classes\grid\column\DateRangePickerColumn;
use app\classes\grid\column\universal\StringColumn;

?>

<?= app\classes\Html::formLabel($this->title = 'Загруженность номеров') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония'],
        ['label' => $this->title],
    ],
]);

$filter = [
    [
        'attribute' => 'date',
        'name' => 'date',
        'label' => 'Период',
        'class' => DateRangePickerColumn::className(),
        'value' => $filterModel->date,
    ],
    [
        'attribute' => 'number',
        'label' => 'Номер',
        'class' => StringColumn::className(),
        'value' => $filterModel->number,
        'options' => [
            'name' => 'number',
        ]
    ]
];

if ($filterModel->number) {
    $first = [
        'label' => 'Интервал',
        'attribute' => 'interval'
    ];
} else {
    $first = [
        'label' => 'Номер',
        'attribute' => 'number'
    ];
}

try {
    echo GridView::widget(
        [
            'dataProvider' => $filterModel->getWorkload(),
            'filterModel' => $filterModel,
            'beforeHeader' => [
                'columns' => $filter
            ],
            'columns' => [
                $first,
                [
                    'label' => 'Количество линий',
                    'attribute' => 'lines_count'
                ],
                [
                    'label' => 'Количество секунд',
                    'attribute' => 'seconds_count'
                ],
                [
                    'label' => 'Загрузка',
                    'attribute' => 'workload'
                ]
            ],
            'filterPosition' => '',
            'panelHeadingTemplate' => <<< HTML
                    <div class="pull-right">
                        {extraButtons}
                        {filterButton}
                        {floatThead}
                        {export}
                    </div>
                    <div class="pull-left">
                        {summary}
                    </div>
                    <h3 class="panel-title">
                        {heading}
                    </h3>
                    <div class="clearfix"></div>
HTML
        ]
    );
} catch (yii\db\Exception $e) {
    if ($e->getCode() == 8) {
        Yii::$app->session->addFlash(
            'error',
            'Запрос слишком тяжелый, чтобы выполниться. 
             Задайте, пожалуйста, другие фильтры'
        );
    } else {
        Yii::$app->session->addFlash('error', "Ошибка выполнения запроса");
    }
}

?>

