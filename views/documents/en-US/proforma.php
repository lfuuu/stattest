<?php

/** @var $document app\classes\documents\DocumentReport */

$hasDiscount = $document->sum_discount > 0;

$currencyWithoutValue = \app\classes\Utils::money('', $document->getCurrency());

$organization = $document->organization;

$director = $organization->director;
$accountant = $organization->accountant;

$payerCompany = $document->getPayer();

$contragent = $payerCompany->contragent;

?>
<html>
<head>
    <meta http-equiv=Content-Type content="text/html;charset=UTF-8">
    <style type="text/css">
        @font-face {
            font-family: 'Exo2';
        }

        * {
            font-family: 'Exo2';
            font-size: 8pt;
        }

        .strong {
            font-weight: bold;
            font-size: 12pt;
        }

        .text, .text strong {
            font-size: 9.5pt;
        }

        .text.text-bordered {
            font-size: 8pt;
            font-weight: bold;
            border-bottom: 2px solid black;
        }

        .title {
            font-weight: bold;
            font-size: 40px;
            border-bottom: 3px solid black;
        }

        .text-right {
            text-align: right;
        }

        .no-margin {
            margin: 0;
        }
    </style>
</head>

<body>
<table width="100%">
    <tr>
        <td width="30%" align="left" valign="top">
            <img src="/images/logo/<?= $organization->logo_file_name ?>" border="0">
        </td>
        <td width="45%" align="left" valign="top">
            <span class="strong"><?= $organization->name ?></span><br />
            <span class="text"><strong>Legal address</strong>: <?= $organization->legal_address ?></span><br />
            <span class="text"><strong>Tax no.</strong>: <?= $organization->tax_registration_id ?></span>
        </td>
        <td width="45%" align="left" valign="top">

            <span class="text"><strong>Bank:</strong> Raiffeisen Bank Zrt.</span>
            <br />
            <br />
            <span class="text"><strong>(HUF) Bank account:</strong><br />
                <span class="text">IBAN: HU34 1201 0611 0160 4117 0010 0009</span>
            <br />
            <br />
            <span class="text"><strong>(EUR) Bank account:</strong><br />
                <span class="text">IBAN: HU13 1201 0611 0160 4117 0020 0006</span>
            <br />
            <br />
            <span class="text"><strong>(USD) Bank account:</strong><br />
                <span class="text">IBAN: HU89 1201 0611 0160 4117 0030 0003</span>
            <br />
            <br />
                <span class="text">BIC / SWIFT: UBRTHUHB</span>
            <br />
            <br />
                <span class="text"><strong>Bank:</strong> TATRA BANKA A.S.</span>
            <br />
            <br />
            <span class="text"><strong>(EUR) Bank account:</strong><br />
                <span class="text">IBAN: SK51 1100 0000 0029 4603 8869</span>
            <br />
            <br />
                <span class="text">BIC / SWIFT: TATRSKBX</span>

        </td>
    </tr>
</table>

<p class="title text-right">PROFORMA INVOICE</p>
<div class="strong text-right" style="margin-top: -30px;">Number : <?= $document->bill->bill_no ?></div>

<div style="display: block; width:100%; margin-top: 80px;"></div>
<table border="0" width="100%">
    <tr>
        <td width="70%" align="left" valign="top" class="text">

            <strong>
                <p class="strong no-margin">CUSTOMER: <?= $contragent->name ?><br /></p><br />
                Address: <?= $contragent->address_jur ?><br />
                TAX ID: <?= $contragent->inn ?><br />
                EU TAX ID: <?= $contragent->inn_euro ?>
            </strong>

        </td>
        <td width="30%" align="left">
            <table border="0" width="100%">
                <tr>
                    <td class="text">Payment method:</td>
                    <td class="text" align="right"><strong>transfer</strong></td>
                </tr>
                <tr>
                    <td class="text">Issue date:</td>
                    <td class="text" align="right"><strong><?= $document->bill->bill_date ?></strong></td>
                </tr>
                <tr>
                    <td class="text">Due date:</td>
                    <td class="text" align="right"><strong><?= $document->bill->pay_bill_until ?></strong></td>
                </tr>
                <tr></tr>
            </table>
        </td>
    </tr>
    <tr>

    </tr>
</table>

<table width="100%" cellspacing="0" style="margin-top: 50px;">
    <colgroup>
        <col width="*" />
        <col width="10%" />
        <col width="10%" />
        <col width="10%" />
        <col width="10%" />
        <col width="5%" />
        <col width="10%" />
    </colgroup>
    <tr>
        <td align="left"  class="text text-bordered">Description</td>
        <td align="right" class="text text-bordered">Net unit price</td>
        <td align="right" class="text text-bordered">Unit</td>
        <td align="right" class="text text-bordered">Net price</td>
        <td align="right" class="text text-bordered">VAT rate</td>
        <td align="right" class="text text-bordered">VAT</td>
        <td align="right" class="text text-bordered">Gross Price</td>
    </tr>
    <?php

    $sumWithoutTax = $sumTax = 0;

    foreach ($document->bill->lines as $line) :
        $sumWithoutTax += $line->sum_without_tax;
        $sumTax += $line->sum_tax;
      ?>
    <tr>
        <td align="left"  style="background-color: #ddd;"><?= $line->item ?></td>
        <td align="right" style="background-color: #ddd;"><?= number_format($document->bill->price_include_vat ? $line->price - $line->sum_tax : $line->price, 4, '.', '') ?></td>
        <td align="right" style="background-color: #ddd;"><?= $line->amount ?></td>
        <td align="right" style="background-color: #ddd;"><?= $line->sum_without_tax ?></td>
        <td align="right" style="background-color: #ddd;"><?= $line->tax_rate ?> %</td>
        <td align="right" style="background-color: #ddd;"><?= $line->sum_tax ?></td>
        <td align="right" style="background-color: #ddd;"><?= $line->sum ?></td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <td align="left" class="text" style="padding-top: 20px; padding-bottom: 20px;"><strong>Total</strong></td>
        <td align="right"></td>
        <td align="right"></td>
        <td align="right" class="text" style="padding-top: 20px; padding-bottom: 20px;"><strong><?= number_format($sumWithoutTax,2, '.' , '') ?></strong></td>
        <td align="right"></td>
        <td align="right" class="text" style="padding-top: 20px; padding-bottom: 20px;"><strong><?= number_format($sumTax,2, '.' , '') ?></strong></td>
        <td align="right" class="text" style="padding-top: 20px; padding-bottom: 20px;"><strong><?= number_format($sumWithoutTax + $sumTax, 2, '.' , '') ?></strong></td>
    </tr>
    <tr>
        <td class="text" align="right" colspan="7">
            <div style="display: block; width:100%; margin-top: 100px;"></div>
            Subtotal: <?= number_format($sumWithoutTax,2, '.' , '') ?> <?= $document->bill->currencyModel->symbol ?><br />
            VAT amount: <?= number_format($sumTax,2, '.' , '') ?> <?= $document->bill->currencyModel->symbol ?><br />
            <span class="text no-margin"><strong>Invoice total&nbsp; <br /><?= number_format($sumWithoutTax + $sumTax, 2, '.' , '') ?>  <?= $document->bill->currencyModel->symbol ?></strong></span>
        </td>
    </tr>
</table>

<div class="text" style="margin-top: 50px; font-size: 13px;">
    Correspondence: <?= $organization->post_address ?><br>
    Phone: <?= $organization->contact_phone ?>
</div>
</body>
</html>
