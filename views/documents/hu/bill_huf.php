<?php

use app\classes\Utils;
use app\classes\Html;

/** @var $document app\classes\documents\DocumentReport */

$hasDiscount = $document->sum_discount > 0;

$currency_w_o_value = Utils::money('', $document->getCurrency());

$company = $document->getCompany();

$payer_company = $document->getPayer();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>Díjbekérő No <?= $document->bill->bill_no; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            <?php readfile(Yii::$app->basePath . '/web/bill.css'); ?>
        </style>
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
                                <?php
                                if ($inline_img):
                                    echo Html::inlineImg('/images/'. $company['logo'], ['width' => 115, 'border' => 0]);
                                else: ?>
                                    <img src="/images/<?= $company['logo']; ?>" width="115" border="0" />
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (isset($company['site']) && !empty($company['site'])): ?>
                                <?= $company['site']; ?>
                            <?php endif; ?>
                        </div>
                        <tr>
                            <td colspan="2" align="center">
                                <?php
                                if ($inline_img):
                                    echo Html::inlineImg(Yii::$app->request->hostInfo . '/utils/qr-code/get?data=' . $document->getQrCode(), [], 'image/gif');
                                else: ?>
                                    <img src="/utils/qr-code/get?data=<?= $document->getQrCode(); ?>" border="0" />
                                <?php endif; ?>

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
        <p>
            <b>Vevő: <?= ($payer_company['head_company'] ? $payer_company['head_company'] . ', ' : '') . $payer_company['company_full']; ?></b>
        </p>

        <table border="1" width="100%" cellspacing="0" cellpadding="2" style="font-size: 15px;">
            <tbody>
                <tr>
                    <td align="center"><b>No</b></td>
                    <td align="center"><b>Megnevezés</b></td>
                    <td align="center"><b>Tört havidíj szorzója</b></td>
                    <td align="center"><b>Nettó egységár,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <td align="center"><b>Nettó ár,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <td align="center"><b>Áfa, &nbsp;<?= $currency_w_o_value; ?></b></td>
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
                    <td>Vezérigazgató</td>
                    <?php if ($document->sendEmail): ?>
                        <td>
                            <?php
                            if (isset($residents['firm_director']['sign'])):
                                $image_options = [
                                    'width' => 115,
                                    'border' => '0',
                                    'align' => 'top',
                                ];

                                if ($residents['firm_director']['sign']['width']):
                                    $image_options['width'] = $residents['firm_director']['sign']['width'];
                                    $image_options['height'] = $residents['firm_director']['sign']['height'];
                                endif;

                                if ($inline_img):
                                    echo Html::inlineImg('/images/'. $residents['firm_director']['sign']['src'], $image_options);
                                else:
                                    array_walk($image_options, function(&$item, $key) {
                                        $item = $key . '="' . $item . '"';
                                    });
                                    ?>
                                    <img src="/image/<?= $residents['firm_director']['sign']['src']; ?>"<?= implode(' ', $image_options); ?> />
                                <?php endif; ?>
                            <?php else: ?>
                                _________________________________
                            <?php endif; ?>
                        </td>
                    <?php else: ?>
                        <td>
                            <br /><br />_________________________________<br /><br />
                        </td>
                    <?php endif; ?>
                    <td>/ Melnikov A.K. /</td>
                </tr>
                <?php if ($document->sendEmail): ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td align=left>
                            <?php if (isset($residents['firma'])):
                                $image_options = [
                                    'width' => 115,
                                    'border' => '0',
                                    'style' => $residents['firma']['style'],
                                ];

                                if ($residents['firma']['width']) {
                                    $image_options['width'] = $residents['firma']['width'];
                                    $image_options['height'] = $residents['firma']['height'];
                                }

                                if ($inline_img):
                                    echo Html::inlineImg('/images/'. $residents['firma']['src'], $image_options);
                                else:
                                    array_walk($image_options, function(&$item, $key) {
                                        $item = $key . '="' . $item . '"';
                                    });
                                    ?>
                                    <img src="/images/<?= $residents['firma']['src']; ?>"<?= implode(' ', $image_options); ?> />
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </body>
</html>
