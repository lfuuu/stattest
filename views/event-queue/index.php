<?php
/**
 * Список очередь событий
 *
 * @var app\classes\BaseView $this
 * @var EventQueueFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeDoubleColumn;
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
        ['label' => $this->title, 'url' => '/event-queue/'],
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
        'filter' => [ // список всех см. в stat/crons/events/handler.php:64
            '' => '----',
            'add_payment' => 'add_payment',
            'yandex_payment' => 'yandex_payment',
            'newbills__update' => 'newbills__update',
            'check__call_chat' => 'check__call_chat',
            'call_chat__add' => 'call_chat__add',
            'client_set_status' => 'client_set_status',
            'usage_virtpbx__insert' => 'usage_virtpbx__insert',
            'actualize_number' => 'actualize_number',
            'check__usages' => 'check__usages',
            'check__voip_old_numbers' => 'check__voip_old_numbers',
            'check__voip_numbers' => 'check__voip_numbers',
            'check__virtpbx3' => 'check__virtpbx3',
            'ats3__sync' => 'ats3__sync',
            'newbills__insert' => 'newbills__insert',
            'add_account' => 'add_account',
            'product_phone_add' => 'product_phone_add',
            'usage_virtpbx__update' => 'usage_virtpbx__update',
            'ats3__disabled_number' => 'ats3__disabled_number',
            'actualize_client' => 'actualize_client',
            'ats3__blocked' => 'ats3__blocked',
            'midnight' => 'midnight',
            'midnight__monthly_fee_msg' => 'midnight__monthly_fee_msg',
            'midnight__clean_pre_payed_bills' => 'midnight__clean_pre_payed_bills',
            'midnight__clean_event_queue' => 'midnight__clean_event_queue',
            'newbills__delete' => 'newbills__delete',
            'product_phone_remove' => 'product_phone_remove',
            'ats3__unblocked' => 'ats3__unblocked',
            'doc_date_changed' => 'doc_date_changed',
            'ats2_numbers_check' => 'ats2_numbers_check',
            'call_chat__del' => 'call_chat__del',
            'cyberplat_payment' => 'cyberplat_payment',
            'update_products' => 'update_products',
            'midnight__lk_bills4all' => 'midnight__lk_bills4all',
            'call_chat__update' => 'call_chat__update',
            'lk_settings_to_mailer' => 'lk_settings_to_mailer',
            'usage_virtpbx__delete' => 'usage_virtpbx__delete',
        ],
    ],
    [
        'attribute' => 'status',
        'options' => [
            'class' => 'event-queue-status-column',
        ],
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => [
            '' => '----',
            'plan' => 'plan',
            'ok' => 'ok',
            'error' => 'error',
            'stop' => 'stop',
        ],
    ],
    [
        'attribute' => 'iteration',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'log_error',
        'format' => 'raw',
        'class' => StringColumn::className(),
        'value' => function (EventQueue $eventQueue) {
            if (!$eventQueue->log_error) {
                return '';
            }
            return Html::tag(
                'button',
                mb_substr($eventQueue->log_error, 0, 20) . '...',
                [
                    'class' => 'btn btn-xs btn-danger',
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
                mb_substr($paramString, 0, 20) . '...',
                [
                    'class' => 'btn btn-xs btn-info',
                    'data-toggle' => 'popover',
                    'data-html' => 'true',
                    'data-placement' => 'bottom',
                    'data-content' => nl2br(htmlspecialchars($paramString)),
                ]
            );
        }
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);

?>
<script type='text/javascript'>
    $(function () {
        $('[data-toggle="popover"]').popover()
    })
</script>
