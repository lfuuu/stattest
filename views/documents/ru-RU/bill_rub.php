<?php

use app\classes\Utils;
use app\classes\Wordifier;
use app\classes\Html;
use app\helpers\MediaFileHelper;

/** @var $document app\classes\documents\DocumentReport */

$hasDiscount = $document->sum_discount > 0;

$currency_w_o_value = Utils::money('', $document->getCurrency());

$organization = $document->organization;

$director = $organization->director;
$accountant = $organization->accountant;

$payer_company = $document->getPayer();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>Счёт &#8470;<?= $document->bill->bill_no; ?></title>
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
                        'organization' => $organization,
                        'payer_company' => $payer_company,
                    ]);
                    ?>
                </td>
                <td align="right">
                    <div style="width: 110px;  text-align: center;padding-right: 10px;">
                        <?php if (MediaFileHelper::checkExists('ORGANIZATION_LOGO_DIR', $organization->logo_file_name)): ?>
                            <?php
                            if ($inline_img):
                                echo Html::inlineImg(MediaFileHelper::getFile('ORGANIZATION_LOGO_DIR', $organization->logo_file_name), ['width' => 115, 'border' => 0]);
                            else: ?>
                                <img src="<?= MediaFileHelper::getFile('ORGANIZATION_LOGO_DIR', $organization->logo_file_name); ?>" width="115" border="0" />
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (isset($company['site']) && !empty($company['site'])): ?>
                            <?= $company['site']; ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($document->bill->clientAccount->client != 'salvus'): ?>
                        <table border="0" align="right">
                            <tr<?= ($document->sum > 300 ? ' bgcolor="#FFD6D6"' : '');?>>
                                <td>Клиент</td>
                                <td><?= $document->bill->clientAccount->client; ?></td>
                            </tr>
                            <?php
                            $color = '';
                            if ($document->bill->clientAccount->manager == 'bnv')
                                $color = '#EEDCA9';
                            if ($document->bill->clientAccount->manager == 'pma')
                                $color = '#BEFFFE';
                            ?>
                            <tr valign=top<?= ($color ? ' bgcolor="' . $color . '"' : ''); ?>>
                                <td>Менеджер</td>
                                <td width="50"><?= str_replace(' ', '&nbsp;', $document->bill->clientAccount->userManager->name); ?></td>
                            </tr>
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
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <hr />

        <center><h2>Счёт &#8470;<?= $document->bill->bill_no; ?></h2></center>

        <p align=right>Дата: <b> <?= Yii::$app->formatter->asDatetime($document->bill->bill_date, 'php:d.m.Y'); ?> г.</b></p>

        <hr />
        <br />
        <p>
            <b>Плательщик: <?= ($payer_company['head_company'] ? $payer_company['head_company'] . ', ' : '') . $payer_company['company_full']; ?></b>
        </p>

        <table border="1" width="100%" cellspacing="0" cellpadding="2" style="font-size: 15px;">
            <tbody>
            <tr>
                <td align="center"><b> п/п</b></td>
                <td align="center"><b>Предмет счета</b></td>
                <td align="center"><b>Количество</b></td>
                <td align="center"><b>Единица измерения</b></td>
                <td align="center"><b>Стоимость,&nbsp;<?= $currency_w_o_value; ?></b></td>
                <?php if ($hasDiscount): ?>
                    <td align="center"><b>Скидка</b></td>
                <?php endif; ?>

                <?php if ($organization->isNotSimpleTaxSystem()): ?>
                    <?php if ($document->bill->price_include_vat): ?>
                        <td align="center"><b>Сумма налога, &nbsp;<?= $currency_w_o_value; ?></b></td>
                        <td align="center"><b>Сумма,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <?php else: ?>
                        <td align="center"><b>Сумма,&nbsp;<?= $currency_w_o_value; ?></b></td>
                        <td align="center"><b>Сумма налога, &nbsp;<?= $currency_w_o_value; ?></b></td>
                        <td align="center"><b>Сумма с учётом налога,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <?php endif; ?>
                <?php else: ?>
                    <td align="center"><b>Сумма,&nbsp;<?= $currency_w_o_value; ?></b></td>
                <?php endif; ?>
            </tr>

            <?php foreach($document->lines as $position => $line): ?>
                <tr>
                    <td align="right"><?= ($position + 1); ?></td>
                    <td><?= $line['item']; ?></td>
                    <td align="center"><?= Utils::mround($line['amount'], 4,6); ?></td>
                    <td align="center">
                        <?php
                        if (isset($line['okei_name']))
                            echo $line['okei_name'];
                        else {
                            if ($line['type'] == 'service')
                                echo '-';
                            else
                                echo 'шт.';
                        }
                        ?>
                    </td>
                    <td align="center"><?= Utils::round($line['price'], 4); ?></td>

                    <?php if ($hasDiscount): ?>
                        <td align="center"><?= Utils::round($line['discount_auto'] + $line['discount_set'], 2); ?></td>
                    <?php endif; ?>

                    <?php if($organization->isNotSimpleTaxSystem()): ?>
                        <?php if ($document->bill->price_include_vat): ?>
                            <td align="center"><?= (!$document->bill->clientAccount->getTaxRate() ? 'без НДС' : Utils::round($line['sum_tax'], 2)); ?></td>
                            <td align="center"><?= Utils::round($line['sum'], 2); ?></td>
                        <?php else: ?>
                            <td align="center"><?= Utils::round($line['sum_without_tax'], 2); ?></td>
                            <td align="center"><?= (!$document->bill->clientAccount->getTaxRate() ? 'без НДС' : Utils::round($line['sum_tax'], 2)); ?></td>
                            <td align="center"><?= Utils::round($line['sum'], 2); ?></td>
                        <?php endif; ?>
                    <?php else: ?>
                        <td align="center"><?= Utils::round($line['sum'], 2); ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>

            <tr>

                <td colspan="<?=$hasDiscount ? '6' : '5'?>" align="right">
                    <div style="padding-top: 3px; height: 15px;">
                        <b>Итого:</b>
                    </div>
                </td>

                <?php if($organization->isNotSimpleTaxSystem()): ?>
                    <?php if ($document->bill->price_include_vat): ?>
                        <td align="center"><?= Utils::round($document->sum_with_tax, 2); ?></td>
                        <td align="center"><?= Utils::round($document->sum, 2); ?></td>
                    <?php else: ?>
                        <td align="center"><?= Utils::round($document->sum_without_tax, 2); ?></td>
                        <td align="center"><?= Utils::round($document->sum_with_tax, 2); ?></td>
                        <td align="center"><?= Utils::round($document->sum, 2); ?></td>
                    <?php endif; ?>
                <?php else: ?>
                    <td align="center"><?= Utils::round($document->sum, 2); ?></td>
                <?php endif; ?>
            </tr>

            <?php if (!$organization->isNotSimpleTaxSystem()): ?>
                <tr>
                    <td colspan="<?=$hasDiscount ? '6' : '5'?>" align="right">
                        <div style="padding-top: 3px; height: 15px;">
                            <b>Без налога (НДС)*</b>
                        </div>
                    </td>
                    <td align="center">-</td>
                </tr>
                <tr>
                    <td colspan="<?=$hasDiscount ? '6' : '5'?>" align="right">
                        <div style="padding-top: 3px; height: 15px;">
                            <b>Всего к оплате:</b>
                        </div>
                    </td>
                    <td align="center"><?= Utils::round($document->sum, 2); ?></td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
        <br />

        <p>
            <i>
                Сумма прописью: <?= Wordifier::Make($document->sum, $document->getCurrency()); ?>
            </i>
        </p>

        <?php if (!$organization->isNotSimpleTaxSystem()): ?>
            <p align="center">
                <b>
                    *НДС не облагается: Упрощённая система налогообложения, Глава 26.2. НК РФ.
                </b>
            </p>
            <br /><br />
        <?php endif; ?>

        <table border="0" align=center cellspacing="1" cellpadding="0">
            <col width="*" />
            <col width="250" />
            <col width="*" />
            <tbody>
            <tr>
                <td><?= $director->post_nominative; ?></td>
                <?php if ($document->sendEmail): ?>
                    <td>
                        <?php if(MediaFileHelper::checkExists('SIGNATURE_DIR', $director->signature_file_name)):
                            $image_options = [
                                'width' => 140,
                                'border' => 0,
                                'align' => 'top',
                            ];

                            if ($inline_img):
                                echo Html::inlineImg(MediaFileHelper::getFile('SIGNATURE_DIR', $director->signature_file_name), $image_options);
                            else:
                                array_walk($image_options, function(&$item, $key) {
                                    $item = $key . '="' . $item . '"';
                                });
                                ?>
                                <img src="<?= MediaFileHelper::getFile('SIGNATURE_DIR', $director->signature_file_name); ?>"<?= implode(' ', $image_options); ?> />
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
                <td>/ <?= $director->name_nominative; ?> /</td>
            </tr>
            <tr>
                <td>Главный бухгалтер</td>
                <?php if ($document->sendEmail) :?>
                    <td>
                        <?php if (MediaFileHelper::checkExists('SIGNATURE_DIR', $accountant->signature_file_name)):
                            $image_options = [
                                'width' => 140,
                                'border' => 0,
                                'align' => 'top',
                            ];

                            if ($inline_img):
                                echo Html::inlineImg(MediaFileHelper::getFile('SIGNATURE_DIR', $accountant->signature_file_name), $image_options);
                            else:
                                array_walk($image_options, function(&$item, $key) {
                                    $item = $key . '="' . $item . '"';
                                });
                                ?>
                                <img src="<?= MediaFileHelper::getFile('SIGNATURE_DIR', $accountant->signature_file_name); ?>"<?= implode(' ', $image_options); ?> />
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
                <td>
                    / <?= $accountant->name_nominative; ?> /
                </td>
            </tr>
            <?php if ($document->sendEmail): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td align=left>
                        <?php if (MediaFileHelper::checkExists('STAMP_DIR', $organization->stamp_file_name)):
                            $image_options = [
                                'width' => 200,
                                'border' => 0,
                                'style' => 'position:relative; left:65; top:-200; z-index:-10; margin-bottom:-170px;',
                            ];

                            if ($inline_img):
                                echo Html::inlineImg(MediaFileHelper::getFile('STAMP_DIR', $organization->stamp_file_name), $image_options);
                            else:
                                array_walk($image_options, function(&$item, $key) {
                                    $item = $key . '="' . $item . '"';
                                });
                                ?>
                                <img src="<?= MediaFileHelper::getFile('STAMP_DIR', $organization->stamp_file_name); ?>"<?= implode(' ', $image_options); ?> />
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if ($document->bill->clientAccount->firma != 'ooocmc' && $document->bill->clientAccount->firma != 'ooomcn'): ?>
            <small>
                Примечание:
                При отсутствии оплаты счета до конца текущего месяца услуги по договору будут приостановлены до полного погашения задолженности.
            </small>
        <?php endif; ?>
    </body>
</html>
