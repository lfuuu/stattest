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

<?php if (false) { //@TODO?>
    {if $zalog} с учетом платежей полученных в обеспечение исполнения обязательств по договору:
    <table>
        {foreach from=$zalog item=z name=zalog}
        <tr>
            <td>{$smarty.foreach.zalog.iteration}.&nbsp;</td>
            <td>{$z.date|mdate:"d.m.Y"}, &#8470;{$z.inv_no} ({$z.items})</td>
            <td>{$z.sum_income|money_currency:$currency}</td>
        </tr>
        {/foreach}
    </table>

    {else}

    {/if}
<?php } ?>
    &nbsp;задолженность
<?php if ($total['income_sum'] > 0.0001) {
    echo 'в пользу ' . $firm->name . ' составляет ' . $total['income_sum'] . ' р.';
} elseif ($total['outcome_sum'] > 0.0001) {
    echo 'в пользу ' . $contragent->name_full . ' составляет ' . $total['outcome_sum'];
} else {
    echo 'отсутствует';
}