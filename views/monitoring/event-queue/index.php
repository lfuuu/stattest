<?php
/**
 * Список очередь событий
 *
 * @var app\classes\BaseView $this
 * @var EventQueueFilter $filterModel
 */

use app\classes\Event;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\EventQueue;
use app\models\filter\EventQueueFilter;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Очередь событий') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title, 'url' => '/monitoring/event-queue/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'attribute' => 'id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'insert_time',
        'class' => DateTimeRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'date',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'next_start',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'event',
        'options' => [
            'class' => 'event-queue-event-column',
        ],
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => ['' => '----'] + Event::$names,
        'value' => function (EventQueue $eventQueue) {
            return isset(Event::$names[$eventQueue->event]) ? Event::$names[$eventQueue->event] : $eventQueue->event;
        }
    ],
    [
        'attribute' => 'status',
        'options' => [
            'class' => 'event-queue-status-column',
        ],
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => ['' => '----'] + EventQueue::$statuses,
        'value' => function (EventQueue $eventQueue) {
            return isset(EventQueue::$statuses[$eventQueue->status]) ? EventQueue::$statuses[$eventQueue->status] : $eventQueue->status;
        }
    ],
    [
        'attribute' => 'iteration',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'log_error',
        'format' => 'raw',
        'class' => StringColumn::className(),
        'contentOptions' => [
            'class' => 'popover-width-auto',
        ],
        'value' => function (EventQueue $eventQueue) {
            if (!$eventQueue->log_error) {
                return '';
            }
            return Html::tag(
                'button',
                $eventQueue->log_error,
                [
                    'class' => 'btn btn-xs btn-danger event-queue-log-error-button text-overflow-ellipsis',
                    'data-toggle' => 'popover',
                    'data-html' => 'true',
                    'data-placement' => 'bottom',
                    'data-content' => nl2br(htmlspecialchars($eventQueue->log_error)),
                ]
            );
        }
    ],
    [
        'attribute' => 'param',
        'format' => 'raw',
        'class' => StringColumn::className(),
        'contentOptions' => [
            'class' => 'popover-width-auto',
        ],
        'value' => function (EventQueue $eventQueue) {
            if (!$eventQueue->param) {
                return '';
            }
            if ($eventQueue->param[0] !== '{') {
                // не json
                return $eventQueue->param;
            }
            $paramArray = json_decode($eventQueue->param, true);
            $paramString = print_r($paramArray, true);
            return Html::tag(
                'button',
                $paramString,
                [
                    'class' => 'btn btn-xs btn-info event-queue-log-param-button text-overflow-ellipsis',
                    'data-toggle' => 'popover',
                    'data-html' => 'true',
                    'data-placement' => 'bottom',
                    'data-content' => nl2br(htmlspecialchars($paramString)),
                ]
            );
        }
    ],
];

if ($filterModel->status == EventQueue::STATUS_STOP) {
    $extraButtons = $this->render('//layouts/_link', [
        'url' => '/monitoring/event-queue/?submitButtonRepeatStopped=1',
        'text' => 'Ошибочные обработать повторно',
        'glyphicon' => 'glyphicon-repeat',
        'params' => [
            'class' => 'btn btn-warning',
        ],
    ]);
} else {
    $extraButtons = '';
}

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'extraButtons' => $extraButtons,
]);

?>
<script type='text/javascript'>
    $(function () {
        var $popovers = $('[data-toggle="popover"]');
        $popovers.length && $popovers.popover();
    })
</script>
