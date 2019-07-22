<?php

/* @var $this \yii\web\View */

use app\classes\grid\GridView;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use kartik\widgets\DatePicker;
use yii\widgets\Breadcrumbs;


/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $contragent \app\models\ClientContragent */
/* @var $firm \app\models\Organization */


echo Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'Бухгалтерия',
        ['label' => $this->title, 'url' => '/report/accounting/pay-report/revise'],
    ],
]);


echo Html::beginForm(['revise'], 'get');
echo '<span class="row"><span class="col-sm-2"><label>От:</label>';
echo DatePicker::widget([
    'name' => 'dateFrom',
    'value' => $dateFrom,
    'pluginOptions' => [
        'format' => 'yyyy-mm-dd',
    ]
]);
echo '</span><span class="col-sm-2"><label>До:</label>';
echo DatePicker::widget([
    'name' => 'dateTo',
    'value' => $dateTo,
    'pluginOptions' => [
        'format' => 'yyyy-mm-dd',
    ]

]);
echo '</span><span class="col-sm-2"><label>Начальное сальдо:</label>';
echo Html::textInput('saldo', $saldo, ['class' => 'pull-left form-control']);
echo '</span><span class="col-sm-2">';
echo Html::submitButton('Фильтровать', [
    'class' => 'pull-left btn btn-primary',
    'style' => 'margin-top: 20px',
    'name' => 'submit'
]);
echo '</span></span>';
echo Html::endForm();

if ($isSubmit) {
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'description',
                'label' => 'Описание',
            ],
            [
                'attribute' => 'income_sum',
                'label' => 'Дебет'
            ],
            [
                'attribute' => 'outcome_sum',
                'label' => 'Кредит'
            ],
        ],
        'isFilterButton' => false
    ]);
}

if (!$contragent) {
    return;
}

$total = end($dataProvider->allModels);
$dateToFormated = (new \DateTimeImmutable($dateTo))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);
?>
    По данным  <?= $firm->name ?> на <?= $dateToFormated ?> г.,
<?php if ($deposit): ?>
    с учетом платежей полученных в обеспечение исполнения обязательств по договору:
    <table>
        <?php
        $i = 0;
        foreach ($deposit as $value):
            $i++; ?>
            <tr>
                <td>    <?= $i ?>.&nbsp;</td>
                <td>    <?= date("d.m.Y", strtotime($value['bill_date'])) ?>&nbsp;</td>
                <td> &#8470;<?= $value['inv_no'] ?>&nbsp;</td>
                <td> (<?= $value['item'] ?>)&nbsp;</td>
                <td>    <?= number_format($value['sum'], 2, ',', '') ?>&nbsp;рублей</td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

    &nbsp;задолженность
<?php if ($deposit_balance > 0.0001) {
    echo 'в пользу ' . $firm->name . ' составляет ' . number_format($deposit_balance, 2, ',', '') . ' рублей.';
} elseif ($deposit_balance < 0.0001) {
    echo 'в пользу ' . $contragent->name_full . ' составляет ' . number_format(-$deposit_balance, 2, ',', '') . ' рублей.';
} else {
    echo 'отсутствует';
}