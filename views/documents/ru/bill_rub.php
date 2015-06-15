<?php

use app\classes\Utils;
use app\classes\Wordifier;

/** @var $document app\classes\documents\DocumentReport */

$isDiscount = 0;
foreach ($document->bill_lines as $position => $line)
     $isDiscount += $line['discount_auto'] + $line['discount_set'];

$currency_w_o_value = Utils::money('', $document->getCurrency());

$company = $document->getCompany();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>Счёт &#8470;<?= $document->bill->bill_no; ?></title>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" />
        <link title=default href="/bill.css" type="text/css" rel="stylesheet" />
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
                <td align="right">
                    <div style="width: 110px;  text-align: center;padding-right: 10px;">
                        <?php if (isset($company['logo']) && !empty($company['logo'])): ?>
                            <img border="0" src="/images/<?= $company['logo']; ?>" width="115" />
                        <?php endif; ?>
                        <?php if (isset($company['site']) && !empty($company['site'])): ?>
                            <?= $company['site']; ?>
                        <?php endif; ?>
                    </div>

                     <?php if ($document->bill->clientAccount->client != 'salvus'): ?>
                        <table border="0" align="right">
                            <tr<?= ($document->summary->value > 300 ? ' bgcolor="#FFD6D6"' : '');?>>
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
                                    <?php if ($document->qr_code !== false): ?>
                                        <img src="/utils/qr-code/get?data=<?= $document->qr_code; ?>" />
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

        <p align=right>Дата: <b> <?= Yii::$app->formatter->asDatetime($document->bill->bill_date, 'dd MMM YYYY'); ?></b></p>

        <hr />
        <br />
        <p>
            <b>Плательщик: <?= ($document->bill->clientAccount->head_company ? $document->bill->clientAccount->head_company . ', ' : '') . $document->bill->clientAccount->company_full; ?></b>
        </p>

        <table border="1" width="100%" cellspacing="0" cellpadding="2" style="font-size: 15px;">
            <tbody>
                <tr>
                    <td align="center"><b> п/п</b></td>
                    <td align="center"><b>Предмет счета</b></td>
                    <td align="center"><b>Количество</b></td>
                    <td align="center"><b>Единица измерения</b></td>
                    <td align="center"><b>Стоимость,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <td align="center"><b>Сумма,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <td align="center"><b><?= ($document->bill->clientAccount->firma == 'mcn_telekom' ? 'НДС 18%': 'Сумма налога'); ?>,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <td align="center"><b>Сумма с учётом налога,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <?php if ($isDiscount): ?>
                        <td align="center"><b>Скидка</b></td>
                        <td align="center"><b>Сумма со скидкой,<br>с учётом налога,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <?php endif; ?>
                </tr>

                <?php foreach($document->bill_lines as $position => $line): ?>
                    <tr>
                        <td align="right"><?= ($position + 1); ?></td>
                        <td><?= $line['item']; ?></td>
                        <td align="center"><?= Utils::mround($line['amount'], 4,6); ?></td>
                        <td align="center">
                            <?php
                            if ($line['okvd_code'])
                                echo $line['okvd_code'] . $line['okei_name'];
                            else {
                                if ($line['type'] == 'service')
                                    echo '-';
                                else
                                    echo 'шт.';
                            }
                            ?>
                        </td>
                        <td align="center"><?= Utils::round($line['price'], 4); ?></td>
                        <td align="center"><?= Utils::round($line['sum_without_tax'], 2); ?></td>
                        <td align="center"><?= ($document->bill->clientAccount->nds_zero/* || $line['line_nds'] == 0*/ ? 'без НДС' : Utils::round($line['sum_tax'], 2)); ?></td>
                        <td align="center"><?= Utils::round($line['sum'], 2); ?></td>
                        <?php if ($isDiscount): ?>
                            <td align="center"><?= Utils::round($line['discount_auto'] + $line['discount_set'], 2); ?></td>
                            <td align="center"><?= Utils::round($line['sum'] - ($line['discount_auto'] + $line['discount_set']), 2); ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="5" align="right">
                        <div style="padding-top: 3px; height: 15px;">
                            <b>Итого:</b>
                        </div>
                    </td>
                    <td align="center"><?= Utils::round($document->summary->without_tax, 2); ?></td>
                    <td align="center">
                        <?php if (!$isDiscount): ?>
                            <?= ($document->bill->clientAccount->nds_zero ? 'без НДС' : Utils::round($document->summary->with_tax, 2)); ?>
                        <?php else: ?>
                            &nbsp;
                        <?php endif; ?>
                    </td>
                    <?php if ($isDiscount): ?>
                        <td align="center">&nbsp;</td>
                        <td align="center"><?= Utils::round($isDiscount, 2); ?></td>
                    <?php endif; ?>
                    <td align="center"><?= Utils::round($document->summary->value - $isDiscount, 2); ?></td>
                </tr>
            </tbody>
        </table>
        <br />

        <p><i>Сумма прописью: <?= Wordifier::Make($document->summary->value - $isDiscount, $document->getCurrency()); ?></i></p>

        <!--table border="0" align=center cellspacing="1" cellpadding="0">
            <tbody>
                <tr>
                    <td>{$firm_director.position}</td>
                    {if isset($emailed) && $emailed==1}
                        <td>
                            {if $firm_director.sign} <img src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firm_director.sign.src}"  border="0" alt="" align="top"{if $firm_director.sign.width} width="{$firm_director.sign.width}" height="{$firm_director.sign.height}"{/if}> {else} _________________________________ {/if}
                         </td>
                    {else}
                        <td><br><br>_________________________________<br><br></td>
                    {/if}
                    <td>/ {$firm_director.name} /</td>
                </tr>
                <tr>
                    <td>Главный бухгалтер</td>
                    {if isset($emailed) && $emailed==1}
                        <td>
                            {if $firm_buh.sign}<img src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firm_buh.sign.src}"  border="0" alt="" align="top"{if $firm_buh.sign.width} width="{$firm_buh.sign.width}" height="{$firm_buh.sign.height}"{/if}>{else} _________________________________ {/if}
                        </td>
                    {else}
                        <td><br><br>_________________________________<br><br></td>
                    {/if}
                    <td>
                        / {$firm_buh.name} /
                    </td>
                </tr>
                {if isset($emailed) && $emailed==1}
                    <tr>
                        <td>&nbsp;</td>
                        <td align=left>
                            {if $firma}<img style='{$firma.style}' src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firma.src}"{if $firma.width} width="{$firma.width}" height="{$firma.height}"{/if}>{/if}
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                {/if}
            </tbody>
        </table-->

        <?php if ($document->bill->clientAccount->firma != 'ooocmc' && $document->bill->clientAccount->firma != 'ooomcn'): ?>
            <small>
                Примечание:
                При отсутствии оплаты счета до конца текущего месяца услуги по договору будут приостановлены до полного погашения задолженности.
            </small>
        <?php endif; ?>
    </body>
</html>
