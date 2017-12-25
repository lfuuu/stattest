<?php

/** @var \app\classes\documents\CreditNoteDocument $document */
$note = $document->bill->creditNote;

if (!$note) {
    return;
}

$contractInfo = $document->bill->clientAccount->contract->contractInfo;

if (!$contractInfo) {
    return;
}

$documentDateFormat = function ($dateStr) {

    $date = new \DateTime($dateStr);

    return "&quot;" . $date->format('d') . "&quot; " . \Yii::$app->formatter->asDate($date, 'MMMM Y') . "г.";
};


$contragent = $document->bill->clientAccount->contragent;

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 5.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Кредит-нота от <?= $documentDateFormat($note->payment_date) ?> на <?= $note->sum ?> руб.</title>
    <style type="text/css">
        @page {
            margin-left: 2.25cm;
            margin-right: 1.5cm;
            margin-top: 2cm;
            margin-bottom: 2cm
        }

        p {
            margin-bottom: 0.25cm;
            direction: ltr;
            line-height: 120%;
            text-align: left;
            orphans: 2;
            widows: 2
        }

        p.western {
            font-family: "Calibri", serif
        }

        p.ctl {
            font-family: "Calibri"
        }
    </style>
</head>
<body lang="ru-RU" dir="ltr">
<p class="western" style="margin-bottom: 0cm; line-height: 100%"><br/>

</p>
<p style="width: 99%; margin-bottom: 0cm; line-height: 100%;text-align: center;"><b>Кредит-нота</b></p>

<p style="margin-bottom: 0cm; line-height: 100%"><br/>

</p>
<p style="margin-bottom: 0cm; line-height: 100%">Дата
    составления: <?= $documentDateFormat($note->payment_date) ?></p>

<p style="margin-bottom: 0cm; line-height: 100%"><br/>

</p>
<p style="margin-bottom: 0cm; line-height: 100%">В связи с
    наступлением обстоятельств, предусмотренных
    Договором № <?= $contractInfo->contract_no ?> от <?= $documentDateFormat($contractInfo->contract_date) ?>
</p>
<p style="margin-bottom: 0cm; line-height: 100%">за предыдущий
    период ваша задолженность за поставленные
    нашей организацией услуги связи
    уменьшается на сумму <?= $note->sum ?> руб., НДС
    не облагается.</p>
<p style="margin-bottom: 0cm; line-height: 100%"><br/>

</p>
<p style="margin-bottom: 0cm; line-height: 100%">&nbsp;&nbsp;&nbsp;&nbsp;</p>
<p style="margin-bottom: 0cm; line-height: 100%">
    <?= $contragent->position ?> _____________ /_<?= $contragent->fio ?>_/</p>
<p style="margin-bottom: 0cm; line-height: 100%; padding-left: 170px;">(подпись)&nbsp;&nbsp;&nbsp;</p>
<p style="margin-bottom: 0cm; line-height: 100%"><br/>

</p>
<p style="margin-bottom: 0cm; line-height: 100%"><a name="_GoBack"></a>
    <br/>

</p>

<p style="margin-bottom: 0cm; line-height: 100%"><br/>

</p>
<p style="margin-bottom: 0cm; line-height: 100%"><font color="#34414a"><font face="Tahoma, serif"><font size="2"
                                                                                                        style="font-size: 9pt"><span
                        style="background: #ffffff">При
выдаче премии денежными средствами,
реализации товаров, работ или услуг не
происходит, и у продавца </span><b><span
                            style="background: #ffffff">отсутствует
база для начисления НДС</span></b><span
                        style="background: #ffffff">
(пп. 1 п. 1 ст. 146 НК РФ). Причем, если
предоставление бонусов не влечет
изменения цены договора купли-продажи
(поставки), то выручку от реализации
товаров продавец определяет без учета
бонусов. Поскольку получение премии не
связано с оказанием покупателем услуг
в пользу продавца, то и у покупателя
бонус не облагается НДС.</span></font></font></font></p>
<p class="western" style="margin-bottom: 0cm; line-height: 100%"><br/>

</p>
</body>
</html>