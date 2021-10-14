<?php

/* @var $this \yii\web\View */

use app\classes\grid\GridView;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\Currency;
use app\models\Language;
use kartik\widgets\DatePicker;
use yii\widgets\Breadcrumbs;


/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $contragent \app\models\ClientContragent */
/* @var $firm \app\models\Organization */

$isShowForm = $format == '';
$lang = $contragent->lang_code;
if ($isShowForm) {
    echo Html::formLabel($this->title);
    echo Breadcrumbs::widget([
        'links' => [
            'Бухгалтерия',
            ['label' => $this->title, 'url' => '/report/accounting/pay-report/revise'],
        ],
    ]);


    echo Html::beginForm(['revise'], 'get', ['id' => 'f_send']);
    echo '<span class="row"><span class="col-sm-2"><label>От:</label>';
    echo DatePicker::widget([
        'name' => 'dateFrom',
        'value' => $dateFrom,
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]);
    echo '</span>';
    echo '<span class="col-sm-2"><label>До:</label>';
    echo DatePicker::widget([
        'name' => 'dateTo',
        'value' => $dateTo,
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]

    ]);
    echo '</span>';

    echo '<span class="col-sm-2"><label>Начальное сальдо:</label>';
    echo Html::textInput('saldo', $saldo, ['class' => 'pull-left form-control']);
    echo '</span>';

    echo '<span class="col-sm-2"><label>Подпись:</label>';
    echo Html::dropDownList('sign', $sign, ['' => ' --- Без подписи --- ', 'director' => 'Директор'], ['class' => 'select2']);
    echo '</span>';

    echo '<span class="col-sm-2"><label>Формат:</label>';
    echo Html::dropDownList('format', '', ['' => ' ----- ', 'html' => 'HTML', 'pdf' => 'PDF'], ['class' => 'select2', 'id' => 'format']);
    echo '</span>';

    echo '<span class="col-sm-1">';
    echo Html::submitButton('Сформировать', [
        'class' => 'pull-left btn btn-primary',
        'style' => 'margin-top: 20px',
        'name' => 'submit',
        'onclick' => 'return sendForm()',
    ]);
    echo '</span>';

    echo '</span>';


    echo Html::hiddenInput('fullscreen', 0, ['id' => 'i_fullscreen']);
    echo Html::hiddenInput('is_pdf', 0, ['id' => 'is_pdf']);

    echo Html::endForm();

    echo <<< JScrip
<script>
    
	function sendForm()
	{
		var format = $('#format').val();
		if (format == 'html' || format == 'pdf') {
			$("#f_send").attr('target','_blank');
		} else {
		    $("#f_send").removeAttr('target');
		}
        return true;
	}
	</script>
JScrip;
} else {
    echo <<<ECHO
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>Акт сверки {$contragent->name_full}</title>
    <META http-equiv=Content-Type content="text/html; charset=utf-8">
    <style>
    .price {
        font-size:15px;
    }
    body {
        color: black;
        font-size: 8pt;
    }
    td {
        color: black;
    }
    thead tr td {
        font-weight:bold;
    }
    h2 {
        text-align:center;
        font-size: 12pt;
    }
    h3 {
        text-align:center;
    }
    p {font-family: 'Times New Roman'; font-size: 8pt;}
    td {font-family: 'Times New Roman'; font-size: 8pt;}
    th {font-family: Verdana; font-size: 6pt;}
    small {font-size: 6.5pt;}
    strong {font-size: 6.5pt;}
    </style>
</head>


<body bgcolor="#FFFFFF" style="BACKGROUND: #FFFFFF" >
ECHO;

}

$date = new DateTime($dateTo);
if ($lang == Language::LANGUAGE_RUSSIAN) {
    unset($currency);
}
if ($isSubmit) {
    ?>
    <br>
    <br>
        <center>
            <h2><?= Yii::t('reconcilliation', 'Reconcilliation act', [], $lang) ?></h2>
            <h3 style="color: black;">
            <?= Yii::t('reconcilliation', 'Description', [
                    'date' => $date->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED),
                    'client_name' => $contragent->name_full,
                    'account_number' => $accountId,
                    'company_name' => $firm->name->value,
                ], $lang) ?>
            <br>
            <br>
        </center>
        
        <TABLE class=price cellSpacing=0 cellPadding=2 border=1>
            <thead>
            <tr>
                <td width=50% colspan=4><?=Yii::t('reconcilliation', 'According', ['name' => $firm->name->value, 'currency' => $currency], $lang) ?></td>
                <td width=50% colspan=4><?=Yii::t('reconcilliation', 'According', ['name' => $contragent->name_full, 'currency' => $currency], $lang) ?></td>
            </tr>
            <tr>
                <td width=4%><?= Yii::t('reconcilliation', 'No', [], $lang) ?> </td>
                <td width=36%><?= Yii::t('reconcilliation', 'Documents', [], $lang) ?></td>
                <td width=5%><?= Yii::t('reconcilliation', 'Debit', [], $lang) ?></td>
                <td width=5%><?=Yii::t('reconcilliation', 'Credit', [], $lang)?></td>
                <td width=4%><?= Yii::t('reconcilliation', 'No', [], $lang) ?> </td>
                <td width=24%><?= Yii::t('reconcilliation', 'Documents', [], $lang) ?></td>
                <td width=5%><?= Yii::t('reconcilliation', 'Debit', [], $lang) ?></td>
                <td width=5%><?= Yii::t('reconcilliation', 'Credit', [], $lang) ?></td>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($dataProvider->allModels as $idx => $item) : ?>
                <tr>
                    <td><?= ($idx + 1) ?></td>
                    <td><?= $item['description'] ?></td>
                    <td align=right><?= ($item['income_sum'] !== '' ? number_format($item['income_sum'], 2, ',', '&nbsp;') : '') ?></td>
                    <td align=right><?= ($item['outcome_sum'] !== '' ? number_format($item['outcome_sum'], 2, ',', '&nbsp;') : '') ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php
}

if (!$contragent) {
    return;
}

$total = end($dataProvider->allModels);
$dateToFormated = (new \DateTimeImmutable($dateTo))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);
?>

<?= Yii::t('reconcilliation', 'According to data', ['company_name' => $firm->name, 'date' => $dateToFormated], $lang) ?>
<?php if ($deposit): ?>
    <?= Yii::t('reconcilliation', 'Deposit', [], $lang); ?>
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
                <td>    <?= number_format($value['sum'], 2, ',', ' ') ?>&nbsp; <?= Yii::t('reconcilliation', 'Currnecy', [], $lang); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

&nbsp; <?= Yii::t('reconcilliation', 'Before debt', [], $lang); ?>&nbsp;
<?php if ($deposit_balance > 0.0001) {
    echo Yii::t('reconcilliation', 'Debt', [
            'company_name' => $firm->name, 
        ], $lang);
    echo Currency::formatCurrencyLang($lang, number_format(abs($deposit_balance), 2, ',', ' '), $currency);
    } elseif ($deposit_balance < -0.0001) {
    echo Yii::t('reconcilliation', 'Debt', [
        'company_name' => $contragent->name_full, 
        ], $lang);
    echo Currency::formatCurrencyLang($lang, number_format(abs($deposit_balance), 2, ',', ' '), $currency);
} else {
   echo Yii::t('reconcilliation', 'Even', [], $lang);
}
?>
    
<div>
    <table border="0" cellpadding="0" cellspacing="5">
        <tr>
            <td colspan="3"><p><?= Yii::t('reconcilliation', 'From', [], $lang); ?> <?= $firm->name->value ?></p></td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td><p><?= Yii::t('reconcilliation', 'From', [], $lang); ?> <?= $contragent->name_full ?></p></td>
        </tr>
        <tr>
            <td colspan="5">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="5">&nbsp;</td>
        </tr>
        <tr>
            <td><?= Yii::t('reconcilliation', 'Head of organization', [], $lang); ?></td>
            <td>______________________________</td>
            <td> <?= $firm->director->name_nominative ?></td>
            <td></td>
            <td>______________________________</td>
        </tr>
        <tr>
            <td></td>
            <td align="center">
                <small><?= Yii::t('reconcilliation', 'Signature', [], $lang); ?></small>
            </td>
            <td></td>
            <td></td>
            <td align="center">
                <small><?= Yii::t('reconcilliation', 'Signature', [], $lang); ?></small>
            </td>
        </tr>
        <tr>
            <td></td>
            <td align="center"><?= Yii::t('reconcilliation', 'MP', [], $lang); ?></td>
            <td></td>
            <td></td>
            <td align="center"><?= Yii::t('reconcilliation', 'MP', [], $lang); ?></td>
        </tr>
    </table>
</div>
<?php if ($sign == 'director') : ?>
    <div style="position:absolute; z-index:100; left:200px; margin-left:-30px;margin-top:<?= (in_array($firm->director->signature_file_name, ['sign_vav.png', 'sign_bnv.png']) ? '-100px;' : '-86px;') ?>">
        <img src="/images/signature/<?= $firm->director->signature_file_name ?>" border="0" alt="" align="top">
    </div>
    <?php if ($firm->stamp_file_name) : ?>
        <div style="position:absolute; z-index:100; left:200px; margin-left:-120px; margin-top:-150px">
            <img src="/images/stamp/<?= $firm->stamp_file_name ?>" width="200" height="200"/>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
if (!$isShowForm) {
    echo <<<ECHO
</body>
</html>
ECHO;
}