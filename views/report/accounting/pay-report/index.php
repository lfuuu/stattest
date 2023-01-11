<?php
/**
 * @var app\classes\BaseView $this
 * @var \app\models\filter\PayReportFilter $filterModel
 */

use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\DropdownColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\OrganizationColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\models\Currency;
use app\models\Payment;
use app\models\PaymentAtol;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Breadcrumbs;

echo app\classes\Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'Бухгалтерия',
        ['label' => $this->title, 'url' => '/report/accounting/pay-report/'],
    ],
]);

$baseView = $this;

$columns = [
    [
        'attribute' => 'client_id',
        'class' => IntegerColumn::class,
    ],

    [
        'attribute' => 'client_name',
        'label' => $filterModel->getAttributeLabel('client_name'),
        'class' => StringColumn::class,
        'format' => 'raw',
        'value' => function (Payment $payment) {
            return Html::tag('small',
                Html::a(
                    $payment->client->contragent->name,
                    ['client/view', 'id' => $payment->client_id],
                    ['target' => '_blank', 'title' => $payment->client->contragent->name_full]
                )
            );
        },
        'headerOptions' => ['style' => 'width: 140px'],
    ],

    [
        'attribute' => 'organization_id',
        'label' => $filterModel->getAttributeLabel('organization_id'),
        'class' => OrganizationColumn::class,
        'format' => 'raw',
        'value' => function (Payment $payment) {
            return $payment->client->contract->organization_id;
        },
        'headerOptions' => ['style' => 'width: 180px'],
    ],

    [
        'attribute' => 'bill_no',
        'format' => 'raw',
        'class' => StringColumn::class,
        'value' => function (Payment $payment) {
            return $payment->bill_no ? Html::a(
                $payment->bill_no,
                ['/', 'module' => 'newaccounts', 'action' => 'bill_view', 'bill' => $payment->bill_no],
                ['target' => '_blank']
            ) : '';
        },
        'headerOptions' => ['style' => 'width: 120px'],
    ],

    [
        'attribute' => 'bill_date',
        'label' => $filterModel->getAttributeLabel('bill_date'),
        'class' => DateRangeDoubleColumn::class,
        'value' => function (Payment $payment) {
            return $payment->bill ? $payment->bill->bill_date : '';
        },
    ],

    [
        'attribute' => 'sum',
        'class' => IntegerRangeColumn::class,
        'headerOptions' => ['style' => 'width: 100px'],
    ],

    [
        'attribute' => 'currency',
        'class' => CurrencyColumn::class,
    ],

    [
        'attribute' => 'payment_date',
        'class' => DateRangeDoubleColumn::class,
    ],

    [
        'attribute' => 'add_date',
        'class' => DateRangeDoubleColumn::class,
    ],

    [
        'attribute' => 'type',
        'class' => DropdownColumn::class,
        'filter' => $filterModel->getTypeList(),
        'value' => function (Payment $payment) {
            return $payment->ecash_operator ? $payment->type . '_' . $payment->ecash_operator : $payment->type;
        },
        'headerOptions' => ['style' => 'min-width: 100px'],
    ],

    [
        'attribute' => 'payment_no',
        'format' => 'raw',
        'class' => StringColumn::class,
        'value' => function (Payment $payment) {
            return Html::tag('small',
                $payment->type == Payment::TYPE_ECASH && $payment->ecash_operator == Payment::ECASH_STRIPE
                    ? $payment->getPaymentStripe()->select('token_id')->scalar()
                    : $payment->payment_no
            );
        },
    ],

    [
        'attribute' => 'comment',
        'format' => 'raw',
        'class' => StringColumn::class,
        'value' => function (Payment $payment) {
            $apiInfo = $payment->apiInfo ? json_decode($payment->apiInfo->request, true) : [];
            return Html::tag('small', $payment->comment . ($apiInfo ? "<br>" .
                    Html::tag('i', $apiInfo['description'], [
                        'class' => 'btn btn-xs btn-default event-queue-log-param-button text-overflow-ellipsis',
                        'aria-hidden' => 'true',
                        'data-toggle' => 'popover',
                        'data-html' => 'true',
                        'data-placement' => 'bottom',
                        'data-content' => nl2br(htmlspecialchars($apiInfo['description'])),
                    ])
                    : ''));
        },
    ],

    /*
        [
            'attribute' => 'add_user',
            'format' => 'raw',
            'class' => UserColumn::class,
            'indexBy' => 'id',
            'value' => function (Payment $payment) {
                return $payment->add_user && $payment->addUser ?
                    Html::tag('div', $payment->addUser->user, ['title' => $payment->addUser->name]) :
                    '';
            },
        ],
    */

    [
        'attribute' => 'uuid_status',
        'label' => $filterModel->getAttributeLabel('uuid_status'),
        'class' => DropdownColumn::class,
        'filter' => $filterModel->getUuidStatusList(),
        'headerOptions' => ['style' => 'min-width: 100px'],
        'format' => 'raw',
        'value' => function (Payment $payment, $key, $index, DropdownColumn $that) {

            $paymentAtol = $payment->paymentAtol;

            if (
                !$paymentAtol
                && $payment->currency === Currency::RUB
                && $payment->type === Payment::TYPE_ECASH
                && array_key_exists($payment->ecash_operator, Payment::$ecash)
                && $payment->ecash_operator !== Payment::ECASH_CYBERPLAT
            ) {
                return Yii::t('common', '(not set)') .
                    ' ' .
                    Html::a(
                        Html::tag('i', '', [
                            'class' => 'glyphicon glyphicon-export',
                            'aria-hidden' => 'true',
                        ]),
                        ['/report/accounting/pay-report/send-to-atol/', 'id' => $payment->id],
                        [
                            'class' => 'btn btn-xs btn-default',
                            'title' => 'Отправить в онлайн-кассу',
                        ]);
            }

            $value = $paymentAtol->uuid_status;

            if (is_array($that->filter) && isset($that->filter[$value])) {
                $content = (string)$that->filter[$value];
            } else {
                $content = $value;
            }

            if ($paymentAtol->uuid_status == PaymentAtol::UUID_STATUS_SENT) {
                $content .= ' ' . Html::a(
                        Html::tag('i', '', [
                            'class' => 'glyphicon glyphicon-refresh',
                            'aria-hidden' => 'true',
                        ]),
                        ['/report/accounting/pay-report/refresh-status/', 'id' => $payment->id],
                        [
                            'class' => 'btn btn-xs btn-default',
                            'title' => 'Запросить в онлайн-кассе текущий статус',
                        ]);
            }

            return $content;
        }
    ],
    [
        'attribute' => 'uuid_log',
        'label' => $filterModel->getAttributeLabel('uuid_log'),
        'format' => 'raw',
        'class' => StringColumn::class,
        'contentOptions' => [
            'class' => 'popover-width-auto',
        ],
        'value' => function (Payment $payment) {
            $paymentAtol = $payment->paymentAtol;
            if (!$paymentAtol) {

                if ($payment->apiInfo && $payment->apiInfo->info_json) {
                    try {
                        $logArray = Json::decode($payment->apiInfo->info_json, true);
                    } catch (\Exception $e) {
                        $logArray = $payment->apiInfo->info_json . '<br>' . $e->getMessage();
                    }
                    $logString = substr(print_r($logArray, true), 0, 10240);
                    return Html::tag(
                        'button',
                        $logString,
                        [
                            'class' => 'btn btn-xs btn-info event-queue-log-param-button text-overflow-ellipsis',
                            'data-toggle' => 'popover',
                            'data-html' => 'true',
                            'data-placement' => 'bottom',
                            'data-content' => nl2br(htmlspecialchars($logString)),
                        ]
                    );
                }

                return Yii::t('common', '(not set)');
            }

            if (!$paymentAtol->uuid_log) {
                return '';
            }

            if ($paymentAtol->uuid_log[0] !== '{') {
                // не json
                return $paymentAtol->uuid_log;
            }

            $logArray = Json::decode($paymentAtol->uuid_log, true);
            $logString = print_r($logArray, true);
            return Html::tag(
                'button',
                $logString,
                [
                    'class' => 'btn btn-xs btn-info event-queue-log-param-button text-overflow-ellipsis',
                    'data-toggle' => 'popover',
                    'data-html' => 'true',
                    'data-placement' => 'bottom',
                    'data-content' => nl2br(htmlspecialchars($logString)),
                ]
            );
        }
    ],
    [
        'attribute' => 'payment_api_log',
        'label' => $filterModel->getAttributeLabel('payment_api_log'),
        'format' => 'raw',
        'class' => StringColumn::class,
        'contentOptions' => [
            'class' => 'popover-width-auto',
        ],
        'value' => function (Payment $payment) {

            if ($payment->apiInfo && $payment->apiInfo->log) {
                return Html::tag(
                    'button',
                    $payment->apiInfo->log,
                    [
                        'class' => 'btn btn-xs btn-info event-queue-log-param-button text-overflow-ellipsis',
                        'data-toggle' => 'popover',
                        'data-html' => 'true',
                        'data-placement' => 'bottom',
                        'data-content' => nl2br(htmlspecialchars($payment->apiInfo->log)),
                    ]
                );
            }

            return Yii::t('common', '(not set)');
        }
    ],
];

$dataProvider = $filterModel->search();
?>

    <div class="well">
        <div class="span12"><b>Итого: <?= $filterModel->total ?></b></div>
    </div>

<?php

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'rowOptions' => function (Payment $payment) {

        $paymentAtol = $payment->paymentAtol;
        if (!$paymentAtol) {
            return [];
        }

        return ['class' => $paymentAtol->getCssClass()];
    }
]);