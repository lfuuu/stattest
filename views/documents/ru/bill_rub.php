<?php

use app\classes\Utils;
use app\classes\Wordifier;

/** @var $document app\classes\documents\DocumentReport */

$hasDiscount = $document->sum_discount > 0;

$currency_w_o_value = Utils::money('', $document->getCurrency());

$company = $document->getCompany();

$residents = $document->getCompanyResidents();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>Счёт &#8470;<?= $document->bill->bill_no; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <base href="<?= Yii::$app->request->hostInfo; ?>" />
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
                                    <img src="/utils/qr-code/get?data=<?= $document->getQrCode(); ?>" />
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <hr />

        <center><h2>Счёт &#8470;<?= $document->bill->bill_no; ?></h2></center>

        <p align=right>Дата: <b> <?= Yii::$app->formatter->asDatetime($document->bill->bill_date, 'dd MMM YYYY г.'); ?></b></p>

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
                    <?php if ($hasDiscount): ?>
                        <td align="center"><b>Скидка</b></td>
                        <td align="center"><b>Сумма со скидкой,<br>с учётом налога,&nbsp;<?= $currency_w_o_value; ?></b></td>
                    <?php endif; ?>
                </tr>

                <?php foreach($document->lines as $position => $line): ?>
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
                        <td align="center"><?= ($document->bill->clientAccount->nds_zero || $line['nds'] == 0 ? 'без НДС' : Utils::round($line['sum_tax'], 2)); ?></td>
                        <td align="center"><?= Utils::round($line['sum'], 2); ?></td>
                        <?php if ($hasDiscount): ?>
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
                    <td align="center"><?= Utils::round($document->sum_without_tax, 2); ?></td>
                    <td align="center">
                        <?php if (!$hasDiscount): ?>
                            <?= ($document->bill->clientAccount->nds_zero ? 'без НДС' : Utils::round($document->sum_with_tax, 2)); ?>
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

        <p><i>Сумма прописью: <?= Wordifier::Make($document->sum - $document->sum_discount, $document->getCurrency()); ?></i></p>

        <table border="0" align=center cellspacing="1" cellpadding="0">
            <tbody>
                <tr>
                    <td><?= $residents['firm_director']['position']; ?></td>
                    <?php if ($document->isMail()): ?>
                        <td>
                            <?php if(isset($residents['firm_director']['sign'])): ?>
                                <img src="/images/<?= $residents['firm_director']['sign']['src']; ?>"  border="0" alt="" align="top"<?= ($residents['firm_director']['sign']['width']? ' width="' . $residents['firm_director']['sign']['width'] . '" height="' . $residents['firm_director']['sign']['height'] . '"' : ''); ?>>
                            <?php else:?>
                                _________________________________
                            <?php endif; ?>
                        </td>
                    <?php else: ?>
                        <td>
                            <br /><br />_________________________________<br /><br />
                        </td>
                    <?php endif; ?>
                    <td>/ <?= $residents['firm_director']['name']; ?> /</td>
                </tr>
                <tr>
                    <td>Главный бухгалтер</td>
                    <?php if ($document->isMail()) :?>
                        <td>
                            <?php if (isset($residents['firm_buh']['sign'])): ?>
                                <img src="/images/<?= $residents['firm_buh']['sign']['src']; ?>"  border="0" alt="" align="top"<?= ($residents['firm_buh']['sign']['width'] ? ' width="' . $residents['firm_buh']['sign']['width'] . '" height="' . $residents['firm_buh']['sign']['height'] . '"' : ''); ?>>
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
                        / <?= $residents['firm_buh']['name']; ?> /
                    </td>
                </tr>
                <?php if ($document->isMail()): ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td align=left>
                            <?php if (isset($residents['firma'])): ?>
                                <img style="<?= $residents['firma']['style']; ?>" src="/images/<?= $residents['firma']['src']; ?>"<?= ($residents['firma']['width'] ? ' width="' . $residents['firma']['width'] . '" height="' . $residents['firma']['height'] . '"' : ''); ?>>
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
