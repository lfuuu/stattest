<?php

use app\models\ClientContract;
use yii\widgets\Breadcrumbs;

/** @var \app\models\ClientAccount $account */

?>

<?= app\classes\Html::formLabel($this->title = 'Счета 2.0') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Бухгалтерия'],
        ['label' => $this->title, 'url' => '/accounting/'],
        ['label' => $account->getAccountTypeAndId(), 'url' => '/accounting/?account_id=' . $account->id],
        ['label' => 'Тип договора: ' . ClientContract::$financialTypes[$account->clientContractModel->financial_type], 'url' => '/accounting/?account_id=' . $account->id],
    ],
]) ?>

<?php

$paysPlus = \app\models\Payment::find()->where(['client_id' => $account->id])->andWhere(['>', 'sum', 0])->all();
$paysMinus = \app\models\Payment::find()->where(['client_id' => $account->id])->andWhere(['<', 'sum', 0])->all();

$invoices = \app\models\Invoice::find()->joinWith('bill b')
    ->where(['b.client_id' => $account->id])
    ->orderBy(['date' => SORT_ASC])
    ->all();

$billsPlus = \app\models\Bill::find()
    ->where(['client_id' => $account->id])
    ->andWhere(['OR', ['>=', 'sum', 0], ['operation_type_id' => \app\models\OperationType::ID_CORRECTION]])
    ->orderBy(['bill_date' => SORT_ASC])
    ->all();

$billsMinus = \app\models\Bill::find()
    ->where(['client_id' => $account->id])
    ->andWhere(['AND', ['<', 'sum', 0], ['NOT', ['operation_type_id' => \app\models\OperationType::ID_CORRECTION]]])
    ->orderBy(['bill_date' => SORT_ASC])
    ->all();

$invoiceExt = \app\models\BillExternal::find()
    ->joinWith('bill b')
    ->with('bill')
    ->where(['b.client_id' => $account->id])
//    ->andWhere(['not', ['ext_invoice_no' => 'AR/0010106/492']])
    ->orderBy([
        new \yii\db\Expression("STR_TO_DATE(ext_invoice_date, '%d-%m-%Y')") => SORT_ASC,
        'bill_date' => SORT_ASC,
        'b.id' => SORT_ASC,
    ])
    ->all();

$billCorrections = array_reduce(\app\modules\uu\models\AccountEntryCorrection::find()
    ->where(['client_account_id' => $account->id])
    ->asArray()
    ->all(),
    function (array $accum, array $value) {
        $accum[$value['bill_no']] = $value;
        return $accum;
    }, []);

//print_r($billCorrections);

$billInvoiceCorrections = array_filter(array_map(function (\app\models\Bill $b) {
    $comment = $b->comment;
    $m = [];
    if (preg_match('/Автоматическая корректировка к счету (\d{6}-\d{6,7}) \((с\/ф №)?(\d)\)/', $comment, $m)) {
        return ['bill_no' => $m[1], 'type_id' => $m[3], 'bill' => $b, 'is_found' => false];
    }
    return null;
}, array_filter($billsPlus, function (\app\models\Bill $b) {
    return $b->operation_type_id == \app\models\OperationType::ID_CORRECTION;
})));

$billInvoiceCorrections = array_reduce($billInvoiceCorrections,
    function (Array $accum, Array $a) {
        $accum[$a['bill_no']][$a['type_id']] = $a;

        return $accum;
    }
    , []);


$invSum = array_reduce($invoices, function ($acum, $i) {
    return $acum + $i->sum;
}, 0);

$billSumPlus = array_reduce($billsPlus, function ($acum, $i) {
//    echo "<br><pre>";
//    print_r($i->getAttributes());
//    echo "</pre>";
    return $acum + $i->sum;
}, 0);

$billSumMinus = array_reduce($billsMinus, function ($acum, $i) {
    return $acum + $i->sum;
}, 0);

$invoiceExtSum = array_reduce($invoiceExt, function ($acum, \app\models\BillExternal $i) {
    return $acum + $i->ext_vat + $i->ext_sum_without_vat;
}, 0);

$paysPlusSum = array_reduce($paysPlus, function ($acum, $i) {
    return $acum + $i->sum;
}, 0);

$paysMinusSum = array_reduce($paysMinus, function ($acum, $i) {
    return $acum + $i->sum;
}, 0);

$paysPlusInv = $paysPlusBills = $paysPlusSum;
$invoiceExtPays = $paysMinusBills = $paysMinusSum;

function nf($d)
{
    $v = number_format($d, 2, '.', ' ');
    return preg_replace('/(^0.00|\.?[0]+)$/', '<span style="color: lightgrey;">$1</span>', $v);
}

?>
<div class="row">
    <div class="col-sm-3">
        <div class="row text-center"><h2>Доходные (с/ф)</h2></div>
        <div class="row">
            <div class="col-sm-6">Сумма с/ф:</div>
            <div class="col-sm-6 text-right"><?= nf($invSum) ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">платежи "+":</div>
            <div class="col-sm-6 text-right"><?= nf($paysPlusSum) ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">Баланс:</div>
            <div class="col-sm-6 text-right"
                 style="color: <?= (abs($paysPlusSum - $invSum) < 0.01 ? 'black' : ($paysPlusSum - $invSum > 0 ? 'green' : 'red')) ?>"><?= nf($paysPlusSum - $invSum) ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="row text-center"><h2>Доходные (счета)</h2></div>
        <div class="row">
            <div class="col-sm-6">Сумма счетов:</div>
            <div class="col-sm-6 text-right"><?= nf($billSumPlus) ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">платежи "+":</div>
            <div class="col-sm-6 text-right"><?= nf($paysPlusSum) ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">Баланс:</div><?php $totalPlus = $paysPlusSum - $billSumPlus; ?>
            <div class="col-sm-6 text-right"
                 style="color: <?= (abs($totalPlus) < 0.01 ? 'black' : ($totalPlus > 0 ? 'green' : 'red')) ?>"><?= nf($totalPlus) ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="row text-center"><h2>Расходные (счета)</h2></div>
        <div class="row">
            <div class="col-sm-6">Сумма счетов:</div>
            <div class="col-sm-6 text-right"><?= nf($billSumMinus) ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">платежи "-":</div>
            <div class="col-sm-6 text-right"><?= nf($paysMinusSum, 2, '.', ' ') ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">Баланс:</div>
            <?php $totalMinus = $billSumMinus - $paysMinusSum; ?>
            <div class="col-sm-6 text-right"
                 style="color: <?= (abs($totalMinus) < 0.01 ? 'black' : ($totalMinus > 0 ? 'green' : 'red')) ?>"><?= nf($totalMinus) ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="row text-center"><h2>Расходные (с/ф)</h2></div>
        <div class="row">
            <div class="col-sm-6">Сумма с/ф:</div>
            <div class="col-sm-6 text-right"><?= nf($invoiceExtSum) ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">платежи "-":</div>
            <div class="col-sm-6 text-right"><?= nf($invoiceExtPays) ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">Баланс:</div>
            <div class="col-sm-6 text-right"
                 style="color: <?= (abs($paysPlusSum - $invSum) < 0.01 ? 'black' : ($paysPlusSum - $invSum > 0 ? 'green' : 'red')) ?>"><?= nf($invoiceExtPays + $invoiceExtSum) ?></div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6"><?php $totalBills = $totalPlus - $totalMinus; ?>
        <div class="text-center" style="border-top: 1px solid gray; padding: 5px;">Итого по счетам: <span
                    style="color: <?= (abs($totalBills) < 0.01 ? 'black' : ($totalBills > 0 ? 'green' : 'red')) ?>"><?= nf($totalBills) ?></span>
        </div>
    </div>
    <div class="col-sm-3"></div>
</div>
<?php

$d = [];

$dataInv = [];

/** @var \app\models\Invoice $invoice */
foreach ($invoices as $invoice) {

    $v = [
        'number' => $invoice->number,
        'link' => $invoice->link,
        'date' => $invoice->date,
        'sum' => round($invoice->sum, 2),
        'is_paid' => $paysPlusInv > $invoice->sum ? 1 : ($paysPlusInv > 0 ? 2 : 0),
        'type' => 'invoice',
    ];


    $dataInv[] = $v;
    $paysPlusInv -= $invoice->sum;

    $date = new DateTimeImmutable($invoice->date);
    addItem($d, $v, $date);

    if (isset($billInvoiceCorrections[$invoice->bill_no][$invoice->type_id])) {
        $billInvoiceCorrections[$invoice->bill_no][$invoice->type_id]['is_found'] = $invoice;
    }
}


$billInvoiceCorrectionIds = array_reduce($billInvoiceCorrections, function ($accum, $value) {
    array_map(function ($val) use (&$accum) {
        $accum[$val['bill']->id] = $val['is_found']->date;
    }, $value);
    return $accum;
}, []);




$dataBillsPlus = [];
$vv = [];
/** @var \app\models\Bill $bill */
foreach ($billsPlus as $bill) {

    $v = [
        'id' => $bill->id,
        'is_correction' => $bill->operation_type_id == \app\models\OperationType::ID_CORRECTION,
        'number' => $bill->bill_no,
        'link' => $bill->link,
        'date' => isset($billInvoiceCorrectionIds[$bill->id]) ? $billInvoiceCorrectionIds[$bill->id] : $bill->bill_date,
        'sum' => $bill->sum,
        'is_paid' => $paysPlusBills > $bill->sum ? 1 : ($paysPlusBills > 0 ? 2 : 0),
        'type' => 'bill',
    ];
    $vv[] = $v;
}

usort($vv, function($a, $b) {
    $aDate = new DateTimeImmutable($a['date']);
    $bDate = new DateTimeImmutable($b['date']);

    if ($aDate == $bDate) {
        return $a['id'] > $b['id'] ? 1 : -1;
    }

    return $aDate > $bDate  ? 1 : -1;
});

foreach($vv as $v) {

    $dataBillsPlus[] = $v;
    $paysPlusBills -= $bill->sum;

    $date = new DateTimeImmutable($v['date']);
    addItem($d, $v, $date);
}



$dataBillsMinus = [];
/** @var \app\models\Bill $bill */
foreach ($billsMinus as $bill) {

    $v = [
        'number' => $bill->bill_no,
        'link' => $bill->link,
        'date' => $bill->bill_date,
        'sum' => $bill->sum,
        'is_paid' => $paysMinusBills <= $bill->sum ? 1 : (round($paysMinusBills, 4) < 0 ? 2 : 0),
        'type' => 'bill_minus',
    ];

    $dataBillsMinus[] = $v;
    $paysMinusBills -= $bill->sum;

    $date = new DateTimeImmutable($bill->bill_date);
    addItem($d, $v, $date);
}

$dataInvoiceExt = [];
/** @var \app\models\BillExternal $inv */
foreach ($invoiceExt as $inv) {

    $sum = $inv->ext_vat + $inv->ext_sum_without_vat;
    $date = new DateTimeImmutable($inv->ext_invoice_date);
    $v = [
        'number' => $inv->ext_invoice_no,
        'link' => $inv->bill->link,
        'date' => $date->format('Y-m-d'),
        'sum' => $sum,
        'is_paid' => $invoiceExtPays <= $sum ? 1 : (round($invoiceExtPays, 4) < 0 ? 2 : 0),
        'type' => 'invoice_minus',
    ];

    $dataInvoiceExt[] = $v;
    $invoiceExtPays -= $sum;

    addItem($d, $v, $date);
}

/** @var \app\models\Payment $pay */
foreach ($paysPlus as $pay) {

    $v = [
        'number' => $pay->payment_no,
        'link' => "",
        'date' => $pay->payment_date,
        'sum' => round($pay->sum, 2),
        'is_paid' => null,
        'type' => 'payment',
    ];

    $date = new DateTimeImmutable($pay->payment_date);
    addItem($d, $v, $date);
}

$vv = [];
/** @var \app\models\Payment $pay */
foreach ($paysMinus as $pay) {

    $v = [
        'number' => $pay->payment_no,
        'link' => "",
        'date' => $pay->payment_date,
        'sum' => round($pay->sum, 2),
        'is_paid' => null,
        'type' => 'payment_minus',
    ];

    $vv[] = $v;
    $date = new DateTimeImmutable($pay->payment_date);
    addItem($d, $v, $date);
}


foreach ($d as $year => &$yearData) {
    foreach ($yearData as $month => &$monthData) {
        ksort($monthData);
    }
    ksort($yearData);
}
ksort($d);

function addItem(&$d, $item, $date)
{
    if (!isset($d[(int)$date->format('Y')][(int)$date->format('m')][(int)$date->format('d')][$item['type']])) {
        $d[(int)$date->format('Y')][(int)$date->format('m')][(int)$date->format('d')][$item['type']] = [];
    }
    $d[(int)$date->format('Y')][(int)$date->format('m')][(int)$date->format('d')][$item['type']][] = $item;
}

foreach ($d as $year => &$yearData) {
    foreach ($yearData as $month => &$monthData) {
        ksort($monthData);

        foreach ($monthData as $day => $dayData) {
            $nDayData = [];
            foreach ($dayData as $type => $values) {
                foreach ($values as $idx => $value) {
                    if (!isset($nDayData[$idx])) {
                        $nDayData[$idx] = [];
                    }
                    $nDayData[$idx][$value['type']] = $value;
                }
            }
            $monthData[$day] = $nDayData;
        }
    }
    ksort($yearData);
}


class row
{
    public $year = '';
    public $month = '';
    public $day = '';

    public $bill = '';
    public $bill_is_correction = false;
    public $bill_minus = '';
    public $invoice = '';
    public $invoice_minus = '';

    public $payment = '';
    public $payment_minus = '';

    public $bill_is_paid = '';
    public $bill_minus_is_paid = '';
    public $invoice_is_paid = '';
    public $invoice_minus_is_paid = '';
}

class rowCorrection extends row
{
    public $bill = '';
    public $sum = '';
    public $date = '';
}

$rr = [];

$bill_is_paid = null;
$bill_minus_is_paid = null;
$invoice_is_paid = null;
$invoice_minus_is_paid = null;

foreach ($d as $year => &$yearData) {
    foreach ($yearData as $month => &$monthData) {
        ksort($monthData);

        foreach ($monthData as $day => $dayData) {
            $row = new row();
            $row->year = $year;
            $row->month = $month;
            $row->day = $day;

            $row->bill_is_paid = $bill_is_paid;
            $row->bill_minus_is_paid = $bill_minus_is_paid;
            $row->invoice_is_paid = $invoice_is_paid;
            $row->invoice_minus_is_paid = $invoice_minus_is_paid;


            foreach ($dayData as $idx => $typeData) {

                if ($idx > 0) {
                    $rr[] = $row;

                    $row = new row();
                    $row->year = $year;
                    $row->month = $month;
                    $row->day = $day;

                    $row->bill_is_paid = $bill_is_paid;
                    $row->bill_minus_is_paid = $bill_minus_is_paid;
                    $row->invoice_is_paid = $invoice_is_paid;
                    $row->invoice_minus_is_paid = $invoice_minus_is_paid;
                }

                foreach ($typeData as $type => $value) {
                    switch ($type) {
                        case 'bill':
                            $bill_is_paid = $value['is_paid'];
                            $row->bill_is_paid = $bill_is_paid;
                            $row->bill = $value;
                            $row->bill_is_correction = $value['is_correction'];

                            if (isset($billCorrections[$value['number']])) {
                                $bc = $billCorrections[$value['number']];
                                $rc = new rowCorrection();
                                $rc->bill = $bc['bill_no'];
                                $rc->date = new DateTimeImmutable($bc['created_at']);
                                $rc->sum = $bc['sum'];

                                $rc->bill_is_paid = $bill_is_paid;
                                $rc->bill_minus_is_paid = $bill_minus_is_paid;
                                $rc->invoice_is_paid = $invoice_is_paid;
                                $rc->invoice_minus_is_paid = $invoice_minus_is_paid;

                                $rr[] = $rc;
                            }

                            break;

                        case 'bill_minus':
                            $bill_minus_is_paid = $value['is_paid'];
                            $row->bill_minus_is_paid = $bill_minus_is_paid;
                            $row->bill_minus = $value;
                            break;

                        case 'invoice':
                            $invoice_is_paid = $value['is_paid'];
                            $row->invoice_is_paid = $invoice_is_paid;
                            $row->invoice = $value;
                            break;

                        case 'invoice_minus':
                            $invoice_minus_is_paid = $value['is_paid'];
                            $row->invoice_minus_is_paid = $invoice_minus_is_paid;
                            $row->invoice_minus = $value;
                            break;

                        case 'payment':
                            $row->payment = $value;
                            break;

                        case 'payment_minus':
                            $row->payment_minus = $value;
                            break;
                    }
                }
            }
            $rr[] = $row;
        }
    }
}

?>




<?php


function cellContentOptions($is_paid, $addClass = '')
{
    return $is_paid === null
        ? ($addClass ? ['class' => $addClass] : [])
        : ['class' => ($is_paid === 1 ? 'success' : ($is_paid === 2 ? 'warning' : 'danger')) . ($addClass ? ' ' . $addClass : '')];
}

?>
<style>
    td {
        padding: 4px !important;
        height: 5px !important;
    }
    .correction_bill {
        color: #0d52bf;
    }
</style>
<div class="row">
    <div class="col-xs-12">
        <?php
        echo \app\classes\grid\GridView::widget([
                'dataProvider' => new \yii\data\ArrayDataProvider([
                    'allModels' => array_reverse($rr),
//                'allModels' => $rr,
                    'pagination' => false,
                ]),
                'panelHeadingTemplate' => '',
                'columns' => [
                    [
                        'label' => 'Дата',
                        'value' => function ($row) {
                            if ($row instanceof rowCorrection) {
                                return '';
                            }
                            $date = (new DateTimeImmutable())->setDate($row->year, $row->month, $row->day)->setTime(0, 0, 0);
                            return \Yii::$app->formatter->asDate($date, 'php:Y-m-d');

                        }
                    ],
                    [
                        'label' => 'Счет +',
                        'format' => 'raw',
                        'value' => function (row $row) {
                            if ($row instanceof rowCorrection) {
                                return \Yii::$app->formatter->asDate($row->date, 'php:Y-m-d');
                            }

                            return $row->bill
                                ? \app\classes\Html::a($row->bill['number'], $row->bill['link'])
                                . ' ' . ($row->bill_is_correction ? \app\classes\Html::tag('span', '(К)', ['title' => 'Корректировочный счет', 'class' => 'correction_bill']) : '')
                                : '';
                        },
                        'contentOptions' => function ($row) {
                            return cellContentOptions($row->bill_is_paid);
                        },
                    ],
                    [
                        'label' => 'Сумма счета +',
                        'format' => 'raw',
                        'value' => function (row $row) {
                            if ($row instanceof rowCorrection) {
                                return nf($row->sum);
                            }
                            return $row->bill ? nf($row->bill['sum']) : '';
                        },
                        'contentOptions' => function ($row) {
                            return cellContentOptions($row->bill_is_paid, 'text-right');
                        },
                    ],
                    [
                        'label' => 'С/ф +',
                        'format' => 'raw',
                        'contentOptions' => function ($row) {
                            return cellContentOptions($row->invoice_is_paid);
                        },

                        'value' => function (row $row) {
                            if ($row instanceof rowCorrection) {
                                return 'корректировка счета';
                            }
                            return $row->invoice ? \app\classes\Html::a($row->invoice['number'], $row->invoice['link']) : '';
                        },
                    ],
                    [
                        'label' => 'С/ф сумма +',
                        'format' => 'raw',
                        'value' => function (row $row) {
                            return $row->invoice ? nf($row->invoice['sum']) : '';
                        },
                        'contentOptions' => function ($row) {
                            return cellContentOptions($row->invoice_is_paid, 'text-right');
                        },
                    ],

                    [
                        'label' => 'Платеж +',
                        'format' => 'raw',
                        'value' => function (row $row) {
                            return $row->payment ? nf($row->payment['sum']) : '';
                        },
                        'contentOptions' => function ($row) {
                            return cellContentOptions(null, 'info text-right');
                        },
                    ],
                    [
                        'label' => 'Платеж -',
                        'format' => 'raw',
                        'value' => function (row $row) {
                            return $row->payment_minus ? nf($row->payment_minus['sum']) : '';
                        },
                        'contentOptions' => function ($row) {
                            return cellContentOptions(null, 'info text-right');
                        },
                    ],

                    [
                        'label' => 'Счет -',
                        'format' => 'raw',
                        'value' => function (row $row) {
                            return $row->bill_minus ? \app\classes\Html::a($row->bill_minus['number'], $row->bill_minus['link']) : '';
                        },
                        'contentOptions' => function ($row) {
                            return cellContentOptions($row->bill_minus_is_paid);
                        },
                    ],
                    [
                        'label' => 'Сумма счета -',
                        'format' => 'raw',
                        'value' => function (row $row) {
                            return $row->bill_minus ? nf($row->bill_minus['sum']) : '';
                        },
                        'contentOptions' => function ($row) {
                            return cellContentOptions($row->bill_minus_is_paid, 'text-right');
                        },
                    ],
                    [
                        'label' => 'С/ф -',
                        'format' => 'raw',
                        'value' => function (row $row) {
                            return $row->invoice_minus ? \app\classes\Html::a($row->invoice_minus['number'], $row->invoice_minus['link']) : '';
                        },
                        'contentOptions' => function ($row) {
                            return cellContentOptions($row->invoice_minus_is_paid);
                        },
                    ],
                    [
                        'format' => 'raw',
                        'value' => function (row $row) {
                            return $row->invoice_minus ? nf($row->invoice_minus['sum']) : '';
                        },
                        'label' => 'С/ф сумма -',
                        'contentOptions' => function ($row) {
                            return cellContentOptions($row->invoice_minus_is_paid, 'text-right');
                        },
                    ],
                ]
            ]
        );
        ?>
    </div>
</div>