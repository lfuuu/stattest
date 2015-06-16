<?php

use app\classes\Utils;

/** @var $document app\classes\documents\DocumentReport */

$hasDiscount = $document->sum_discount > 0;

$currency_w_o_value = Utils::money('', $document->getCurrency());

$company = $document->getCompany();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>Díjbekérő No <?= $document->bill->bill_no; ?></title>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" />
        <link title="default" href="/bill.css" type="text/css" rel="stylesheet" />
    </head>

    <body bgcolor="#FFFFFF" style="background:#FFFFFF">
        <table width="100%">
            <tr>
                <td>
                    <?php
                    echo Yii::$app->view->renderFile($document->getHeaderTemplate() . '.php', [
                        'document' => $document
                    ]);
                    ?>
                </td>
                <td align=right>
                    <table border="0" align="right">
                        <div style="width: 110px;  text-align: center;padding-right: 10px;">
                            <?php if (isset($company['logo']) && !empty($company['logo'])): ?>
                                <img border="0" src="/images/<?= $company['logo']; ?>" width="115" />
                            <?php endif; ?>
                            <?php if (isset($company['site']) && !empty($company['site'])): ?>
                                <?= $company['site']; ?>
                            <?php endif; ?>
                        </div>
                        <tr>
                            <td colspan="2" align="center">
                                <img src="/utils/qr-code/get?data=<?= $document->getQrCode(); ?>" />
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
        <hr />


        <center><h2>Díjbekérő No <?= $document->bill->bill_no; ?></h2></center>

        <p align=right>Dátum <b><?= Yii::$app->formatter->asDatetime($document->bill->bill_date, 'php:Y.m.d'); ?></b></p>

        <hr />
        <br />
        <p><b>Vevő: Napsütéses Idő</b></p>

        <table border="1" width="100%" cellspacing="0" cellpadding="2" style="font-size: 15px;">
            <tbody>
                <tr>
                    <td align="center"><b>No</b></td>
                    <td align="center"><b>Megnevezés</b></td>
                    <td align="center"><b>Me</b></td>
                    <td align="center"><b>Nettó egységár,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <td align="center"><b>Nettó ár,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <td align="center"><b>Áfa értéke, &nbsp;<?= $currency_w_o_value; ?></b></td>
                    <td align="center"><b>Bruttó ár,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <?php if ($hasDiscount): ?>
                        <td align="center"><b>Áfa érték</b></td>
                        <td align="center"><b>ÁFA összesen,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <?php endif; ?>
                </tr>

                <?php foreach ($document->lines as $position => $line): ?>
                    <tr>
                        <td align="right"><?= ($position + 1); ?></td>
                        <td><?= $line['item']; ?></td>
                        <td align="center"><?= Utils::mround($line['amount'], 4,6); ?></td>
                        <td align="center"><?= Utils::round($line['price'], 4); ?></td>
                        <td align="center"><?= Utils::round($line['sum_without_tax'], 2); ?></td>
                        <td align="center"><?= Utils::round($line['sum_tax'], 2); ?></td>
                        <td align="center"><?= Utils::round($line['sum'], 2); ?></td>
                        <?php if ($hasDiscount): ?>
                            <td align="center"><?= Utils::round($line['discount_auto'] + $line['discount_set'], 2); ?></td>
                            <td align="center"><?= Utils::round($line['sum'] - ($line['discount_auto'] + $line['discount_set']), 2); ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="4" align="right">
                        <div style="padding-top: 3px; height: 15px;">
                            <b>Összesen:</b>
                        </div>
                    </td>
                    <td align="center"><?= Utils::round($document->sum_without_tax, 2); ?></td>
                    <td align="center">
                        <?php if (!$hasDiscount): ?>
                            <?= Utils::round($document->sum_with_tax, 2); ?>
                        <?php else: ?>
                            &nbsp;
                        <?php endif; ?>
                    </td>
                    <?php if ($hasDiscount): ?>
                        <td align="center">&nbsp;</td>
                        <td align="center"><?= Utils::round($document->sum_discount, 2); ?></td>
                    <?php endif; ?>
                    <td align="center"><?= Utils::round($document->sum - $document->sum_discount, 2); ?></td>
                </tr>

            </tbody>
        </table>
        <br />

        <table border="0" align=center cellspacing="1" cellpadding="0">
            <tbody>
                <tr>
                    <td>Vezérigazgatója</td>
                    <td><br><br>_________________________________<br><br></td>
                    <td>/ Melnikov A.K. /</td>
                </tr>
                <tr>
                    <td>Főkönyvelő</td>
                    <td><br><br>_________________________________<br><br></td>
                    <td>
                        / Melnikov A.K. /
                    </td>
                </tr>
            </tbody>
        </table>

    </body>
</html>
