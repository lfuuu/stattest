<?php

/** @var \app\classes\documents\DocumentReport $document */

use app\classes\Html;
use app\helpers\MediaFileHelper;
use app\models\Currency;

$organization = $document->organization;
$bill = $document->bill;
$organizationSwift = $organization->getSettlementAccount(\app\models\OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_SWIFT);
$organizationIban = $organization->getSettlementAccount(\app\models\OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_IBAN);
$contragent = $bill->clientAccount->contragent;

$hDate = function ($dateStr) {
    return (new DateTime($dateStr))->format('d.m.Y');
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<head>
    <title>Счёт &#8470;<?= $document->bill->bill_no; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <?php if ($inline_img) : ?>
        <style type="text/css">
            <?= file_get_contents(Yii::$app->basePath .'/web/css/documents/bill_usd.css') ?>
        </style>
    <?php else : ?>
        <link href="/css/documents/bill_usd.css" rel="stylesheet"/>
    <?php endif; ?>
</head>

<body bgcolor="#FFFFFF" style="background:#FFFFFF">
<table width="100%">
    <tr>
        <td width="50%">
            <table>
                <tr>
                    <td>Ship To:</td>
                    <td><?= $document->bill->clientAccount->contragent->address_jur ?></td>
                </tr>
                <tr>
                    <td colspan="2"><?= $organization->legal_address ?></td>
                </tr>
                <tr>
                    <td>Beneficiary Account Number:</td>
                    <td><?php
                        $ibanProperties = $organizationIban->getProperties()->asArray()->all();

                        $data = [];

                        foreach ([Currency::EUR, Currency::USD] as $currency) {
                            if (isset($ibanProperties['bank_account_' . $currency])
                                && isset($ibanProperties['bank_account_' . $currency]['value'])
                                && trim($ibanProperties['bank_account_' . $currency]['value'])
                            ) {
                                $data[] = $ibanProperties['bank_account_' . $currency]['value'] . ' (' . $currency . ')';
                            }
                        }

                        echo implode("</br>", $data);

                        ?></td>
                </tr>
                <tr>
                    <td>SWIFT:</td>
                    <td><?= $organizationSwift->bank_bik ?></td>
                </tr>
                <tr>
                    <td>Beneficiary’s Bank:</td>
                    <td><?= $organizationSwift->bank_name ?></td>
                </tr>

                <tr>
                    <td>Beneficiary’s Bank Address::</td>
                    <td><?= $organizationSwift->bank_address ?></td>
                </tr>

            </table>
        </td>
        <td align="right"  width="50%">
            <div style="width: 110px;text-align: center;padding-right: 10px;">
                <?php if (MediaFileHelper::checkExists('ORGANIZATION_LOGO_DIR', $organization->logo_file_name)): ?>
                    <?php
                    if ($inline_img):
                        echo Html::inlineImg(MediaFileHelper::getFile('ORGANIZATION_LOGO_DIR', $organization->logo_file_name), ['width' => 115, 'border' => 0]);
                    else: ?>
                        <img src="<?= MediaFileHelper::getFile('ORGANIZATION_LOGO_DIR', $organization->logo_file_name); ?>"
                             width="115" border="0"/>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (isset($company['site']) && !empty($company['site'])): ?>
                    <?= $company['site']; ?>
                <?php endif; ?>
            </div>

            <?php if ($document->bill->clientAccount->client != 'salvus'): ?>
                <table border="0" align="right">
                    <tr<?= ($document->sum > 300 ? ' bgcolor="#FFD6D6"' : ''); ?>>
                        <td>Client</td>
                        <td><?= $document->bill->clientAccount->client; ?></td>
                    </tr>
                    <tr valign=top>
                        <td>Account Manager</td>
                        <td width="50"><?= str_replace(' ', '&nbsp;', $document->bill->clientAccount->userManager->name); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <?php
                            if ($inline_img):
                                echo Html::inlineImg(Yii::$app->request->hostInfo . '/utils/qr-code/get?data=' . $document->getQrCode(), [], 'image/gif');
                            else: ?>
                                <img src="/utils/qr-code/get?data=<?= $document->getQrCode(); ?>" border="0"/>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>
        </td>
    </tr>
</table>

<h1>Invoice No<?= $bill->bill_no ?></h1>
<h2>Date: <?= $hDate($bill->bill_date) ?></h2>
<h2>Invoice Time Zone: +00:00</h2>

<br>
<br>
<br>
<b>Bill To:</b> <?= $contragent->name_full ?>,</br>
<?= $contragent->address_jur ?>,</br>
<?= $contragent->inn_euro ?>
<br>
<br>
<br>

<table width="100%" class="lines_table">
    <thead>
    <th>No</th>
    <th>Description</th>
    <th>Billing period</th>
    <th>Volume, min</th>
    <th>Amount, USD</th>
    <th>VAT rate, %</th>
    <th>VAT amount, USD</th>
    <th>Amount inc. VAT, USD</th>
    </thead>
    <tbody>
    <?php
    $total = ['amount' => 0, 'sum' => 0];
    foreach ($bill->lines as $idx => $line) : ?>
        <tr>
            <td align="center"><?= ($idx + 1); ?></td>
            <td align="center"><?= $line->item ?></td>
            <td align="center"><?= $hDate($line->date_from) . ' - ' . $hDate($line->date_to) ?></td>
            <td align="center"><?= $line->amount ?></td>
            <td align="center"><?= $line->price ?></td>
            <td align="center"><?= $line->tax_rate ?></td>
            <td align="center"><?= $line->sum_tax ?></td>
            <td align="center"><?= $line->sum ?></td>
        </tr>
        <?php

        $total['amount'] += $line->sum - $line->sum_tax;
        $total['tax'] += $line->sum_tax;
        $total['sum'] += $line->sum;
    endforeach; ?>
    <tr>
        <td colspan="4" align="right"><b>Total Amount Due:</b></td>
        <td align="center"><?= number_format($total['amount'], 2) ?></td>
        <td align="center">&nbsp;</td>
        <td align="center"><?= number_format($total['tax'], 2) ?></td>
        <td align="center"><?= $total['sum'] ?></td>
    </tr>
    </tbody>
</table>
<br>
<br>
<br>
<br>


<p>Managing Director: <?= $organization->director->name_nominative ?></p>
<br>
<br>

<p>Signature ________________________</p>

</body>
</html>