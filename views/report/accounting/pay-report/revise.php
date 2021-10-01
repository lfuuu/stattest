<?php

/* @var $this \yii\web\View */

use app\classes\grid\GridView;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\Language;
use kartik\widgets\DatePicker;
use yii\widgets\Breadcrumbs;


/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $contragent \app\models\ClientContragent */
/* @var $firm \app\models\Organization */

$isShowForm = $format == '';
$isRussian = $contragent->lang_code == Language::LANGUAGE_RUSSIAN;
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

if ($isSubmit) {
    // $isRussian = 
    ?>
    <br>
    <br>
    <?php if ($isRussian) : ?>
        <center>
            <h2>АКТ СВЕРКИ</h2>
            <h3 style="color: black;">взаимных расчетов по состоянию на <?= (new DateTime($dateTo))->format("d.m.Y") ?> г.
                <br>между <?= $contragent->name_full ?>
                лицевой счет № <?= $accountId ?> <br>и <?= $firm->name->value ?></h3>
            <br>
            <br>
        </center>
        
        <TABLE class=price cellSpacing=0 cellPadding=2 border=1>
            <thead>
            <tr>
                <td width=50% colspan=4>По данным <?= $firm->name->value ?>, руб.</td>
                <td width=50% colspan=4>По данным <?= $contragent->name_full ?>, руб.</td>
            </tr>
            <tr>
                <td width=4%>&#8470; п/п</td>
                <td width=36%>Наименование операции,<br>документы</td>
                <td width=5%>Дебет</td>
                <td width=5%>Кредит</td>
                <td width=4%>&#8470; п/п</td>
                <td width=24%>Наименование операции,<br>документы</td>
                <td width=11%>Дебет</td>
                <td width=11%>Кредит</td>
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
    <?php else : ?>
        <center>
        <h2>Reconcilliation Act</h2>
        <h3 style="color: black;">Of mutual payments as of <?= (new DateTime($dateTo))->format("d.m.Y") ?>
            <br>between <?= $contragent->name_full ?>
            (personal account № <?= $accountId ?>) <br>and <?= $firm->name->value ?></h3>
        <br>
        <br>
        </center>
        <TABLE class=price cellSpacing=0 cellPadding=2 border=1>
            <thead>
            <tr>
                <td width=50% colspan=4>According to <?= $firm->name->value ?>, $.</td>
                <td width=50% colspan=4>According to <?= $contragent->name_full ?>, $.</td>
            </tr>
            <tr>
                <td width=4%>&#8470; N</td>
                <td width=36%>Operation type,<br>documents</td>
                <td width=5%>Debit</td>
                <td width=5%>Credit</td>
                <td width=4%>&#8470; п/п</td>
                <td width=36%>Operation type,<br>documents</td>
                <td width=11%>Debit</td>
                <td width=11%>Credit</td>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($dataProvider->allModels as $idx => $item) : ?>
                <tr>
                    <td><?= ($idx + 1) ?></td>
                    <?php 
                        $date = (new DateTime($item['date']))->format('d.m.Y');
                        if ($item['type'] == 'saldo') {
                            $item['description'] = 'Balance as of ' . $date;
                        } elseif ($item['type'] == 'inv') {
                            if ($item['inv_no'] == 3) {
                                $item['description'] = 'The act of transferring equipment on bail ' . $date;
                            } else {
                                if ($item['inv_no'] != 4) {
                                    $item['description'] = 'Invoice ';
                                } else {
                                    $item['description'] = 'Waybill ';
                                }
                                $item['description'] .= $item['inv_no'] . ' ' . $date;
                            }
                        } elseif ($item['type'] == 'pay') {
                            $item['description'] = 'Payment ' . $item['pay_no'] . ' ' . $date;
                        } elseif ($item['type'] == 'creditnote') {
                            $item['description'] = 'Credit note on ' . $date;
                        } elseif ($item['type'] == 'period') {
                            $item['description'] = 'Period transactions'; 
                        }
                    ?>
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
    <?php endif ?>
    <?php
}

if (!$contragent) {
    return;
}

$total = end($dataProvider->allModels);
$dateToFormated = (new \DateTimeImmutable($dateTo))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);
?>
<?php if ($isRussian) :?>
    
    По данным  <?= $firm->name ?> на <?= $dateToFormated ?> г.,
<?php else : ?> 
    According to <?= $firm->name ?> data as of <?= $dateToFormated ?>,
<?php endif ?>
   
<?php if ($deposit): ?>
    <?php if ($isRussian) :?>
        с учетом платежей полученных в обеспечение исполнения обязательств по договору:
    <?php else : ?> 
        including payments recieved in order to fulfill contract requirements:
    <?php endif ?>
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
                <td>    <?= number_format($value['sum'], 2, ',', ' ') ?>&nbsp; <?php if ($isRussian) :?>рублей <?php else : ?> $ <?php endif ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>


<?php if ($isRussian) :?>
    &nbsp;задолженность
    <?php if ($deposit_balance > 0.0001) {
        echo 'в пользу ' . $firm->name . ' составляет ' . number_format($deposit_balance, 2, ',', ' ') . ' рублей.';
    } elseif ($deposit_balance < -0.0001) {
        echo 'в пользу ' . $contragent->name_full . ' составляет ' . number_format(-$deposit_balance, 2, ',', ' ') . ' рублей.';
    } else {
        echo 'отсутствует';
    }
    ?>
<?php else : ?> 
    &nbsp; 
    <?php if ($deposit_balance > 0.0001) {
        echo 'the amout of debt in favour of ' . $firm->name . ' amounts to $' . number_format($deposit_balance, 2, ',', ' ');
    } elseif ($deposit_balance < -0.0001) {
        echo 'the amout of debt in favour of ' . $contragent->name_full. ' amounts to $' . number_format($deposit_balance, 2, ',', ' ');
    } else {
        echo 'there is no debt on each side.';
    }
    ?>
<?php endif ?>
    


    <div>
        <table border="0" cellpadding="0" cellspacing="5">
            <tr>
                <td colspan="3"><p><?php if ($isRussian) :?>От <?php endif ?><?= $firm->name->value ?></p></td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td><p><?php if ($isRussian) :?>От <?php endif ?><?= $contragent->name_full ?></p></td>
            </tr>
            <tr>
                <td colspan="5">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="5">&nbsp;</td>
            </tr>
            <tr>
                <td><?php if ($isRussian) :?>Руководитель организации <?php else : ?> Head of organization<?php endif ?></td>
                <td>______________________________</td>
                <td> <?= $firm->director->name_nominative ?></td>
                <td></td>
                <td>______________________________</td>
            </tr>
            <tr>
                <td></td>
                <td align="center">
                    <?php if ($isRussian) :?>
                        <small>(подпись)</small>
                    <?php else : ?>
                        <small>(signature)</small>
                    <?php endif ?>
                </td>
                <td></td>
                <td></td>
                <td align="center">
                    <?php if ($isRussian) :?>
                        <small>(подпись)</small>
                    <?php else : ?>
                        <small>(signature)</small>
                    <?php endif ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td align="center"><?php if ($isRussian) :?> М.П. <?php endif ?></td>
                <td></td>
                <td></td>
                <td align="center"><?php if ($isRussian) :?> М.П. <?php endif ?></td>
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