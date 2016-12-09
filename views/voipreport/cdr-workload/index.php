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
use yii\widgets\Pjax;
use app\classes\grid\column\DateRangePickerColumn;
use app\classes\grid\column\universal\StringColumn;

?>

<?= app\classes\Html::formLabel($this->title = 'Загрузка номера') ?>
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

Pjax::begin([
    'formSelector' => false,
    'linkSelector' => false,
    'enableReplaceState' => true,
    'timeout' => 180000,
]);
echo GridView::widget([
    'dataProvider' => $filterModel->getWorkload(),
    'filterModel' => $filterModel,
    'beforeHeader' => [
        'columns' => $filter
    ],
    'columns' => [
        [
            'label' => 'Интервал',
            'attribute' => 'interval'
        ],
        [
            'label' => 'Количество линий',
            'attribute' => 'lines_count'
        ],
        [
            'label' => 'Количество минут',
            'attribute' => 'minutes_count'
        ],
        [
            'label' => 'Загрузка',
            'attribute' => 'workload'
        ]
    ],
    'pjax' => true,
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
]);
Pjax::end();

?>

