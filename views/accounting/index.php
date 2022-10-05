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

$paysPlus = \app\models\Payment::find()->where(['client_id' => $account->id])->andWhere(['>', 'sum', 0])->sum('sum') ?: 0;
$paysMinus = \app\models\Payment::find()->where(['client_id' => $account->id])->andWhere(['<', 'sum', 0])->sum('sum') ?: 0;

$paysPlusInv = $paysPlusBills = $paysPlus;
$invoiceExtPays = $paysMinusBills = $paysMinus;

$invoices = \app\models\Invoice::find()->joinWith('bill b')
    ->where(['b.client_id' => $account->id])
    ->orderBy(['date' => SORT_ASC])
    ->all();

$billsPlus = \app\models\Bill::find()
    ->where(['client_id' => $account->id])
    ->andWhere(['>=', 'sum', 0])
    ->orderBy(['bill_date' => SORT_ASC])
    ->all();

$billsMinus = \app\models\Bill::find()
    ->where(['client_id' => $account->id])
    ->andWhere(['<', 'sum', 0])
    ->orderBy(['bill_date' => SORT_ASC])
    ->all();

$invoiceExt = \app\models\BillExternal::find()
    ->joinWith('bill b')
    ->where(['b.client_id' => $account->id])
//    ->andWhere(['not', ['ext_invoice_no' => 'AR/0010106/492']])
    ->orderBy([
        new \yii\db\Expression("STR_TO_DATE(ext_invoice_date, '%m-%d-%Y')") => SORT_ASC,
        'bill_date' => SORT_ASC,
        'b.id' => SORT_ASC,
    ])
    ->all();

$invSum = array_reduce($invoices, function ($acum, $i) {
    return $acum + $i->sum;
}, 0);

$billSumPlus = array_reduce($billsPlus, function ($acum, $i) {
    return $acum + $i->sum;
}, 0);

$billSumMinus = array_reduce($billsMinus, function ($acum, $i) {
    return $acum + $i->sum;
}, 0);

$invoiceExtSum = array_reduce($invoiceExt, function ($acum, \app\models\BillExternal $i) {
    return $acum + $i->ext_vat + $i->ext_sum_without_vat;
}, 0);


function nf($d) {
    $v =  number_format($d, 2, '.', ' ');
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
            <div class="col-sm-6 text-right"><?= nf($paysPlus) ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">Баланс:</div>
            <div class="col-sm-6 text-right"
                 style="color: <?= (abs($paysPlus - $invSum) < 0.01 ? 'black' : ($paysPlus - $invSum > 0 ? 'green' : 'red')) ?>"><?= nf($paysPlus - $invSum) ?></div>
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
            <div class="col-sm-6 text-right"><?= nf($paysPlus) ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">Баланс:</div><?php $totalPlus = $paysPlus - $billSumPlus; ?>
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
            <div class="col-sm-6 text-right"><?= nf($paysMinus, 2, '.', ' ') ?></div>
        </div>
        <div class="row">
            <div class="col-sm-6">Баланс:</div>
            <?php $totalMinus = $billSumMinus - $paysMinus ; ?>
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
                 style="color: <?= (abs($paysPlus - $invSum) < 0.01 ? 'black' : ($paysPlus - $invSum > 0 ? 'green' : 'red')) ?>"><?= nf($invoiceExtPays + $invoiceExtSum) ?></div>
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
$dataInv = [];

/** @var \app\models\Invoice $invoice */
foreach ($invoices as $invoice) {

    $dataInv[] = [
        'number' => $invoice->number,
        'link' => $invoice->link,
        'date' => $invoice->date,
        'sum' => round($invoice->sum, 2),
        'is_paid' => $paysPlusInv > $invoice->sum ? 1 : ($paysPlusInv > 0 ? 2 : 0),
    ];

    $paysPlusInv -= $invoice->sum;
}

$dataBillsPlus = [];
/** @var \app\models\Bill $bill */
foreach ($billsPlus as $bill) {

    $dataBillsPlus[] = [
        'number' => $bill->bill_no,
        'link' => $bill->link,
        'date' => $bill->bill_date,
        'sum' => $bill->sum,
        'is_paid' => $paysPlusBills > $bill->sum ? 1 : ($paysPlusBills > 0 ? 2 : 0),
    ];

    $paysPlusBills -= $bill->sum;
}

$dataBillsMinus = [];
/** @var \app\models\Bill $bill */
foreach ($billsMinus as $bill) {

    $dataBillsMinus[] = [
        'number' => $bill->bill_no,
        'link' => $bill->link,
        'date' => $bill->bill_date,
        'sum' => $bill->sum,
        'is_paid' => $paysMinusBills <= $bill->sum ? 1 : (round($paysMinusBills, 4) < 0 ? 2 : 0),
    ];

    $paysMinusBills -= $bill->sum;
}

$dataInvoiceExt = [];
/** @var \app\models\BillExternal $inv */
foreach ($invoiceExt as $inv) {

    $sum = $inv->ext_vat + $inv->ext_sum_without_vat;
    $dataInvoiceExt[] = [
        'number' => $inv->ext_invoice_no,
        'link' => $inv->bill->link,
        'date' => $inv->ext_invoice_date,
        'sum' => $sum,
        'is_paid' => $invoiceExtPays <= $sum ? 1 : (round($invoiceExtPays, 4) < 0 ? 2 : 0),
    ];

    $invoiceExtPays -= $sum;
}


function getGrid($models)
{
    return \app\classes\grid\GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => array_reverse($models),
            'pagination' => false,
        ]),
        'panelHeadingTemplate' => '',
        'rowOptions' => function ($model) {
            return ['class' => $model['is_paid'] === 1 ? 'success' : ($model['is_paid'] === 2 ? 'warning' : 'danger')];
        },
        'columns' => [
            [
                'attribute' => 'number',
                'label' => '№ документа',
                'format' => 'raw',
                'value' => function ($model) {
                    return \app\classes\Html::a($model['number'], $model['link']);
                },
            ],
            [
                'attribute' => 'date',
                'label' => 'Дата',
            ],
            [
                'attribute' => 'sum',
                'format' => 'raw',
                'value' => function($model) {
                    return nf($model['sum']);
                },
                'label' => 'Сумма',
                'contentOptions' => ['class' => 'text-right'],
            ]
        ]
    ]);

}
?>
<div class="row">
    <div class="col-sm-3">
        <?=getGrid($dataInv) ?>
    </div>
    <div class="col-sm-3">
        <?=getGrid($dataBillsPlus) ?>
    </div>
    <div class="col-sm-3">
        <?=getGrid($dataBillsMinus) ?>
    </div>
    <div class="col-sm-3">
        <?=getGrid($dataInvoiceExt) ?>
    </div>
</div>